<?php

namespace App\Http\Controllers;

use App\Http\Requests\Clinic\StoreAnnouncementRequest;
use App\Http\Requests\Clinic\StoreOfferRequest;
use App\Http\Requests\Clinic\UpdateClinicAboutRequest;
use App\Http\Requests\Clinic\UpdateClinicProfileRequest;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicAnnouncement;
use App\Models\ClinicOffer;
use App\Models\ClinicSchedule;
use App\Models\Doctor;
use App\Models\DoctorSessionLog;
use App\Services\AppointmentBookingException;
use App\Services\AppointmentBookingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Clinic area: /clinic
 */
class ClinicController extends Controller
{
    /**
     * Summary of local appointments and active doctor schedules.
     */
    public function dashboard(): View
    {
        $clinic = $this->clinic();
        $today = today()->toDateString();

        $todayAppointments = $clinic->appointments()
            ->with(['patient', 'doctor'])
            ->onDate($today)
            ->orderBy('appointment_time')
            ->get();

        $upcomingSchedules = $clinic->schedules()
            ->with('doctor')
            ->onOrAfter($today)
            ->ordered()
            ->limit(10)
            ->get();

        $upcomingDoctorIds = $clinic->schedules()
            ->onOrAfter($today)
            ->pluck('doctor_id')
            ->unique()
            ->values();

        $stats = [
            'today' => $todayAppointments->count(),
            'todayCompleted' => $todayAppointments->where('status', Appointment::STATUS_COMPLETED)->count(),
            'todayRemaining' => $todayAppointments->where('status', Appointment::STATUS_ACTIVE)->count(),
            'totalAppointments' => $clinic->appointments()->count(),
            'upcomingSchedules' => $clinic->schedules()->onOrAfter($today)->count(),
            'assignedDoctors' => $upcomingDoctorIds->count(),
            'capacityToday' => $clinic->schedules()->onDate($today)->sum('patient_capacity'),
            'doctorsOnline' => DoctorSessionLog::open()
                ->whereIn('doctor_id', $upcomingDoctorIds->all())
                ->count(),
        ];

        return view('clinic.dashboard', [
            'clinic' => $clinic,
            'todayAppointments' => $todayAppointments,
            'upcomingSchedules' => $upcomingSchedules,
            'stats' => $stats,
            'today' => $today,
        ]);
    }

    /**
     * Localised appointment list for the clinic (filterable by date / status).
     */
    public function appointments(Request $request): View
    {
        $clinic = $this->clinic();

        $date = $request->string('date')->trim()->toString() ?: today()->toDateString();
        $status = $request->string('status')->trim()->toString();
        $attendance = $request->string('attendance')->trim()->toString();

        $appointments = $clinic->appointments()
            ->with(['patient', 'doctor'])
            ->when(
                $date === 'all',
                fn ($q) => $q,
                fn ($q) => $q->whereDate('appointment_date', $date)
            )
            ->when(filled($status), fn ($q) => $q->where('status', $status))
            ->when($attendance === 'arrived', fn ($q) => $q->checkedIn())
            ->when($attendance === 'not_arrived', fn ($q) => $q->notCheckedIn())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(20)
            ->withQueryString();

        return view('clinic.appointments.index', [
            'clinic' => $clinic,
            'appointments' => $appointments,
            'date' => $date,
            'status' => $status,
            'attendance' => $attendance,
            'statuses' => Appointment::statuses(),
            'summary' => [
                'total' => $clinic->appointments()->count(),
                'active' => $clinic->appointments()->active()->count(),
                'completed' => $clinic->appointments()->status(Appointment::STATUS_COMPLETED)->count(),
                'upcoming' => $clinic->appointments()
                    ->onDate($date === 'all' ? today()->toDateString() : $date)
                    ->count(),
            ],
        ]);
    }

    /**
     * Mark patient arrival from the clinic / reception desk.
     */
    public function checkIn(Appointment $appointment, AppointmentBookingService $bookings): RedirectResponse
    {
        abort_unless($appointment->clinic_id === $this->clinic()->clinic_id, 403);

        try {
            $bookings->checkIn($appointment);
        } catch (AppointmentBookingException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Patient '.$appointment->patient->full_name.' marked as arrived. '
            .'They have been asked to rate the doctor.');
    }

    /* ---------------------------------------------------------------------
     | Clinic details (name, contact number and map location)
     * ------------------------------------------------------------------- */

    /**
     * Edit the details patients see: name, area, phone, address and map pin.
     */
    public function editProfile(): View
    {
        return view('clinic.profile', ['clinic' => $this->clinic()]);
    }

    public function updateProfile(UpdateClinicProfileRequest $request): RedirectResponse
    {
        $clinic = $this->clinic();

        $clinic->fill([
            'clinic_name' => $request->string('clinic_name')->trim()->toString(),
            'area' => $request->string('area')->trim()->toString(),
            'contact_number' => $request->string('contact_number')->trim()->toString(),
            'address' => $request->string('address')->trim()->toString() ?: null,
            // An empty coordinate box means "let Google Maps find the address".
            'latitude' => $request->filled('latitude') ? (float) $request->input('latitude') : null,
            'longitude' => $request->filled('longitude') ? (float) $request->input('longitude') : null,
        ]);

        // An empty email box leaves the clinic's login address untouched.
        if ($request->filled('email')) {
            $clinic->email = $request->string('email')->trim()->toString();
        }

        $clinic->save();

        return back()->with('success', 'Your clinic details have been updated and are live on the website.');
    }

    /* ---------------------------------------------------------------------
     | About section (banner image + clinic description)
     * ------------------------------------------------------------------- */

    /**
     * Edit the public "about" block of the clinic.
     */
    public function editAbout(): View
    {
        $clinic = $this->clinic();

        return view('clinic.about', [
            'clinic' => $clinic,
            'doctors' => $this->clinicDoctors(),
        ]);
    }

    public function updateAbout(UpdateClinicAboutRequest $request): RedirectResponse
    {
        $clinic = $this->clinic();

        $clinic->fill(['about' => $request->input('about')]);

        if ($request->boolean('remove_banner') && filled($clinic->banner_image)) {
            Storage::disk('public')->delete($clinic->banner_image);
            $clinic->banner_image = null;
        }

        if ($request->hasFile('banner_image')) {
            $clinic->banner_image = $request->file('banner_image')->store('clinic-banners', 'public');
        }

        $clinic->save();

        return back()->with('success', 'Your clinic profile has been updated and is now live on the website.');
    }

    /* ---------------------------------------------------------------------
     | New doctor announcements
     | ------------------------------------------------------------------- */

    public function announcements(): View
    {
        $clinic = $this->clinic();

        return view('clinic.announcements.index', [
            'clinic' => $clinic,
            'announcements' => $clinic->announcements()->with('doctor')->latestFirst()->get(),
            'doctors' => Doctor::orderBy('first_name')->orderBy('last_name')->get(),
        ]);
    }

    public function storeAnnouncement(StoreAnnouncementRequest $request): RedirectResponse
    {
        $clinic = $this->clinic();

        $clinic->announcements()->create([
            'doctor_id' => $request->integer('doctor_id') ?: null,
            'department' => $request->string('department')->trim()->toString(),
            'joining_date' => $request->string('joining_date')->toString(),
            'joining_time' => ClinicSchedule::normalizeTime($request->string('joining_time')->toString()),
            'message' => $request->input('message'),
            // The switch is on by default, so an absent key means "publish".
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Announcement published. It now appears on the landing page slider.');
    }

    public function toggleAnnouncement(ClinicAnnouncement $announcement): RedirectResponse
    {
        abort_unless($announcement->clinic_id === $this->clinic()->clinic_id, 403);

        $announcement->update(['is_active' => ! $announcement->is_active]);

        return back()->with('success', 'Announcement '.($announcement->is_active ? 'published' : 'hidden').' successfully.');
    }

    public function destroyAnnouncement(ClinicAnnouncement $announcement): RedirectResponse
    {
        abort_unless($announcement->clinic_id === $this->clinic()->clinic_id, 403);

        $announcement->delete();

        return back()->with('success', 'Announcement removed.');
    }

    /* ---------------------------------------------------------------------
     | Discount offers
     | ------------------------------------------------------------------- */

    public function offers(): View
    {
        $clinic = $this->clinic();

        return view('clinic.offers.index', [
            'clinic' => $clinic,
            'offers' => $clinic->offers()->latestFirst()->get(),
        ]);
    }

    public function storeOffer(StoreOfferRequest $request): RedirectResponse
    {
        $clinic = $this->clinic();

        $clinic->offers()->create([
            'title' => $request->string('title')->trim()->toString(),
            'description' => $request->input('description'),
            'discount_percent' => $request->integer('discount_percent'),
            'valid_from' => $request->date('valid_from'),
            'valid_until' => $request->date('valid_until'),
            // The switch is on by default, so an absent key means "publish".
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Offer published. It now appears on the landing page slider.');
    }

    public function toggleOffer(ClinicOffer $offer): RedirectResponse
    {
        abort_unless($offer->clinic_id === $this->clinic()->clinic_id, 403);

        $offer->update(['is_active' => ! $offer->is_active]);

        return back()->with('success', 'Offer '.($offer->is_active ? 'published' : 'hidden').' successfully.');
    }

    public function destroyOffer(ClinicOffer $offer): RedirectResponse
    {
        abort_unless($offer->clinic_id === $this->clinic()->clinic_id, 403);

        $offer->delete();

        return back()->with('success', 'Offer removed.');
    }

    /* ---------------------------------------------------------------------
     | Internals
     | ------------------------------------------------------------------- */

    /**
     * Doctors this clinic has published a schedule for, with their experience.
     *
     * @return Collection<int, Doctor>
     */
    protected function clinicDoctors()
    {
        return $this->clinic()
            ->doctors()
            ->withCount(['reviews as public_reviews_count' => fn ($query) => $query->public()])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    protected function clinic(): Clinic
    {
        /** @var Clinic $clinic */
        $clinic = Auth::guard('clinic')->user();

        abort_unless($clinic instanceof Clinic, 403);

        return $clinic;
    }
}

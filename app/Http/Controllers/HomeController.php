<?php

namespace App\Http\Controllers;

use App\Http\Requests\Public\StoreContactQueryRequest;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicAnnouncement;
use App\Models\ClinicOffer;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\SystemQuery;
use App\Services\ClinicProfileService;
use App\Services\DoctorRatingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public, unauthenticated pages: landing page, clinic directory and contact form.
 */
class HomeController extends Controller
{
    public function index(Request $request, DoctorRatingService $ratings): View
    {
        $area = $request->string('area')->trim()->toString();

        $areas = Clinic::query()
            ->approved()
            ->whereNotNull('area')
            ->distinct()
            ->orderBy('area')
            ->pluck('area');

        $clinics = Clinic::query()
            ->approved()
            ->inArea($area)
            ->withCount('schedules')
            ->orderBy('clinic_name')
            ->limit(9)
            ->get();

        return view('welcome', [
            'area' => $area,
            'areas' => $areas,
            'clinics' => $clinics,
            'totalClinics' => Clinic::approved()->count(),
            'totalDoctors' => Doctor::count(),
            'totalPatients' => Patient::count(),
            'totalAppointments' => Appointment::count(),
            'announcements' => $this->activeAnnouncements(),
            'offers' => $this->activeOffers(),
            'topDoctors' => $ratings->leaderboard(limit: 10),
        ]);
    }

    /**
     * New-doctor promotions from approved clinics, for the landing page slider.
     *
     * @return Collection<int, ClinicAnnouncement>
     */
    protected function activeAnnouncements()
    {
        $approvedClinicIds = Clinic::query()->approved()->pluck('clinic_id');

        return ClinicAnnouncement::query()
            ->with(['clinic', 'doctor'])
            ->active()
            ->whereIn('clinic_id', $approvedClinicIds)
            ->orderByRaw('joining_date IS NULL, joining_date ASC')
            ->orderByDesc('announcement_id')
            ->limit(8)
            ->get();
    }

    /**
     * Current discount offers from approved clinics, same slider.
     *
     * @return Collection<int, ClinicOffer>
     */
    protected function activeOffers()
    {
        $approvedClinicIds = Clinic::query()->approved()->pluck('clinic_id');

        return ClinicOffer::query()
            ->with('clinic')
            ->current()
            ->whereIn('clinic_id', $approvedClinicIds)
            ->latestFirst()
            ->limit(8)
            ->get();
    }

    /**
     * Full area-wise clinic directory.
     */
    public function clinics(Request $request): View
    {
        $area = $request->string('area')->trim()->toString();

        $areas = Clinic::query()
            ->approved()
            ->whereNotNull('area')
            ->distinct()
            ->orderBy('area')
            ->pluck('area');

        $clinics = Clinic::query()
            ->approved()
            ->inArea($area)
            ->withCount('schedules')
            ->orderBy('area')
            ->orderBy('clinic_name')
            ->paginate(12)
            ->withQueryString();

        return view('public.clinics', compact('area', 'areas', 'clinics'));
    }

    /**
     * Public profile of one clinic: about section, banner, doctors and reviews.
     */
    public function clinicProfile(Clinic $clinic, ClinicProfileService $profile): View
    {
        abort_unless($clinic->is_approved, 404, 'This clinic is not available publicly.');

        return view('public.clinic_profile', $profile->forClinic($clinic));
    }

    /**
     * Contact Us submissions are stored in `system_queries`.
     */
    public function storeQuery(StoreContactQueryRequest $request): RedirectResponse
    {
        SystemQuery::create([
            'user_name' => $request->string('user_name')->trim()->toString(),
            'email' => $request->string('email')->trim()->lower()->toString(),
            'contact_number' => $request->string('contact_number')->trim()->toString(),
            'message' => $request->string('message')->trim()->toString(),
        ]);

        return redirect()
            ->route('home')
            ->withFragment('contact')
            ->with('success', 'Thank you, '.$request->string('user_name')->trim().'. Your query has been submitted and our team will contact you soon.');
    }
}

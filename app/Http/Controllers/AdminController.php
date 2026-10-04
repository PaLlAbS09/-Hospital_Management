<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreDoctorRequest;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorSessionLog;
use App\Models\Patient;
use App\Models\SystemQuery;
use App\Notifications\ClinicApproved;
use App\Services\AppointmentBookingException;
use App\Services\AppointmentBookingService;
use App\Support\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'clinics' => Clinic::count(),
            'approvedClinics' => Clinic::approved()->count(),
            'pendingClinics' => Clinic::where('status', Clinic::STATUS_PENDING)->count(),
            'rejectedClinics' => Clinic::where('status', Clinic::STATUS_REJECTED)->count(),
            'doctors' => Doctor::count(),
            'patients' => Patient::count(),
            'appointments' => Appointment::count(),
            'activeAppointments' => Appointment::active()->count(),
            'completedAppointments' => Appointment::status(Appointment::STATUS_COMPLETED)->count(),
            'noShowAppointments' => Appointment::status(Appointment::STATUS_NO_SHOW)->count(),
            'todayAppointments' => Appointment::onDate(today()->toDateString())->count(),
            'activeQueries' => SystemQuery::recent()->count(),
            'doctorsOnline' => DoctorSessionLog::open()->count(),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'pendingClinics' => Clinic::where('status', Clinic::STATUS_PENDING)
                ->orderBy('created_at')
                ->limit(5)
                ->get(),
            'recentAppointments' => Appointment::with(['patient', 'doctor', 'clinic'])
                ->orderByDesc('appointment_date')
                ->orderByDesc('appointment_time')
                ->limit(6)
                ->get(),
            'recentQueries' => SystemQuery::latestFirst()->limit(5)->get(),
        ]);
    }

    public function clinics(Request $request): View
    {
        $status = $request->string('status')->trim()->toString();
        $search = $request->string('search')->trim()->toString();

        $clinics = Clinic::query()
            ->when(
                in_array($status, ['pending', 'approved', 'rejected'], true),
                fn ($q) => $q->where('status', $status)
            )
            ->when(filled($search), function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('clinic_name', 'like', '%'.$search.'%')
                        ->orWhere('area', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('contact_number', 'like', '%'.$search.'%');
                });
            })
            ->withCount(['schedules', 'appointments'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->orderBy('clinic_name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.clinics.index', [
            'clinics' => $clinics,
            'status' => $status,
            'search' => $search,
            'counts' => [
                'all' => Clinic::count(),
                'pending' => Clinic::where('status', Clinic::STATUS_PENDING)->count(),
                'approved' => Clinic::approved()->count(),
                'rejected' => Clinic::where('status', Clinic::STATUS_REJECTED)->count(),
            ],
        ]);
    }

    public function approveClinic(Clinic $clinic): RedirectResponse
    {
        $clinic->update(['status' => Clinic::STATUS_APPROVED]);

        $emailSent = Notifier::send($clinic, new ClinicApproved($clinic));

        return back()->with('success', $clinic->clinic_name.' has been approved. The clinic can now sign in.'
            .($emailSent ? '' : ' (The approval email could not be delivered.)'));
    }

    public function rejectClinic(Clinic $clinic): RedirectResponse
    {
        $clinic->update(['status' => Clinic::STATUS_REJECTED]);

        return back()->with('success', $clinic->clinic_name.' has been rejected.');
    }

    public function doctors(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $doctors = Doctor::query()
            ->search($search)
            ->withCount(['schedules', 'appointments'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.doctors.index', compact('doctors', 'search'));
    }

    public function createDoctor(): View
    {
        return view('admin.doctors.create');
    }

    public function storeDoctor(StoreDoctorRequest $request): RedirectResponse
    {
        $doctor = Doctor::create([
            'first_name' => $request->string('first_name')->trim()->toString(),
            'last_name' => $request->string('last_name')->trim()->toString(),
            'specialization' => $request->string('specialization')->trim()->toString(),
            'email' => $request->string('email')->trim()->lower()->toString(),
            'contact' => $request->string('contact')->trim()->toString(),
            'password' => Hash::make($request->string('password')->toString()),
            'experience_years' => $request->integer('experience_years') ?: null,
            'experience_note' => $request->input('experience_note'),
        ]);

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Dr. '.$doctor->full_name.' has been added and can now sign in.');
    }

    public function destroyDoctor(Doctor $doctor): RedirectResponse
    {
        if ($doctor->schedules()->exists() || $doctor->appointments()->exists()) {
            return back()->with('error', 'Dr. '.$doctor->full_name.' still has clinic schedules or appointments on record and cannot be deleted.');
        }

        $name = $doctor->full_name;

        $doctor->sessionLogs()->delete();
        $doctor->delete();

        return back()->with('success', 'Dr. '.$name.' has been deleted.');
    }

    public function patients(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $patients = Patient::query()
            ->search($search)
            ->withCount('appointments')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.patients.index', compact('patients', 'search'));
    }

    public function showPatient(Patient $patient): View
    {
        $appointments = $patient->appointments()
            ->with(['doctor', 'clinic'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->get();

        return view('admin.patients.show', [
            'patient' => $patient,
            'appointments' => $appointments,
            'stats' => [
                'total' => $appointments->count(),
                'active' => $appointments->where('status', Appointment::STATUS_ACTIVE)->count(),
                'completed' => $appointments->where('status', Appointment::STATUS_COMPLETED)->count(),
                'noShow' => $appointments->where('status', Appointment::STATUS_NO_SHOW)->count(),
                'cancelled' => $appointments->whereIn('status', Appointment::cancelledStatuses())->count(),
            ],
        ]);
    }

    public function appointments(Request $request): View
    {
        $status = $request->string('status')->trim()->toString();
        $date = $request->string('date')->trim()->toString();
        $search = $request->string('search')->trim()->toString();
        $attendance = $request->string('attendance')->trim()->toString();

        $appointments = Appointment::query()
            ->with(['patient', 'doctor', 'clinic'])
            ->when(filled($status), fn ($q) => $q->where('status', $status))
            ->when(filled($date), fn ($q) => $q->whereDate('appointment_date', $date))
            ->when($attendance === 'arrived', fn ($q) => $q->checkedIn())
            ->when($attendance === 'not_arrived', fn ($q) => $q->notCheckedIn())
            ->search($search)
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(20)
            ->withQueryString();

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'status' => $status,
            'date' => $date,
            'search' => $search,
            'attendance' => $attendance,
            'statuses' => Appointment::statuses(),
        ]);
    }

    /**
     * Mark patient arrival from the admin / reception desk.
     */
    public function checkInAppointment(Appointment $appointment, AppointmentBookingService $bookings): RedirectResponse
    {
        try {
            $bookings->checkIn($appointment);
        } catch (AppointmentBookingException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Patient marked as arrived for '.$appointment->appointment_date->format('d M Y').' at '.$appointment->formatted_time.'.');
    }

    /**
     * Manually trigger the no-show sweep (admin / reception desk).
     */
    public function cancelNoShows(AppointmentBookingService $bookings): RedirectResponse
    {
        if (! Appointment::hasDatabaseColumn('checked_in_at')) {
            return back()->with('error', 'Arrival tracking columns are missing. Please run `php artisan migrate --force` and try again.');
        }

        $count = $bookings->cancelExpiredNoShows();

        return back()->with('success', "Checked slot times: {$count} no-show appointment(s) auto-cancelled and emailed.");
    }

    public function sessionLogs(Request $request): View
    {
        $doctorId = $request->integer('doctor_id') ?: null;

        $logs = DoctorSessionLog::query()
            ->with('doctor')
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->latestFirst()
            ->paginate(25)
            ->withQueryString();

        return view('admin.session_logs.index', [
            'logs' => $logs,
            'doctors' => Doctor::orderBy('first_name')->get(),
            'doctorId' => $doctorId,
            'openSessions' => DoctorSessionLog::open()->count(),
        ]);
    }

    public function queries(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $queries = SystemQuery::query()
            ->search($search)
            ->latestFirst()
            ->paginate(15)
            ->withQueryString();

        return view('admin.queries.index', [
            'queries' => $queries,
            'search' => $search,
            'activeQueries' => SystemQuery::recent()->count(),
            'totalQueries' => SystemQuery::count(),
        ]);
    }

    public function destroyQuery(SystemQuery $query): RedirectResponse
    {
        $name = $query->user_name;
        $query->delete();

        return back()->with('success', 'The query submitted by '.$name.' has been deleted.');
    }
}

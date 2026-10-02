<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorSessionLog;
use App\Services\AppointmentBookingException;
use App\Services\AppointmentBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return back()->with('success', 'Patient '.$appointment->patient->full_name.' marked as arrived.');
    }

    protected function clinic(): Clinic
    {
        /** @var Clinic $clinic */
        $clinic = Auth::guard('clinic')->user();

        abort_unless($clinic instanceof Clinic, 403);

        return $clinic;
    }
}

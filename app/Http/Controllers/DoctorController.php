<?php

namespace App\Http\Controllers;

use App\Http\Requests\Doctor\UpdatePrescriptionRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSessionLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Doctor area: /doctor
 */
class DoctorController extends Controller
{
    /**
     * Today's queue and upcoming patient visits.
     */
    public function dashboard(): View
    {
        $doctor = $this->doctor();
        $today = today()->toDateString();

        $todayQueue = $doctor->appointments()
            ->with(['patient', 'clinic'])
            ->onDate($today)
            ->orderBy('appointment_time')
            ->get();

        $upcoming = $doctor->appointments()
            ->with(['patient', 'clinic'])
            ->active()
            ->whereDate('appointment_date', '>', $today)
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(10)
            ->get();

        $stats = [
            'todayTotal' => $todayQueue->count(),
            'todayPending' => $todayQueue->where('status', Appointment::STATUS_ACTIVE)->count(),
            'todayCompleted' => $todayQueue->where('status', Appointment::STATUS_COMPLETED)->count(),
            'totalAppointments' => $doctor->appointments()->count(),
            'completed' => $doctor->appointments()->status(Appointment::STATUS_COMPLETED)->count(),
            'prescriptions' => $doctor->appointments()->whereNotNull('prescription_details')->count(),
            'clinics' => $doctor->schedules()->whereDate('schedule_date', '>=', $today)->count(),
        ];

        return view('doctor.dashboard', [
            'doctor' => $doctor,
            'todayQueue' => $todayQueue,
            'upcoming' => $upcoming,
            'stats' => $stats,
            'sessionLog' => $this->currentSessionLog(),
            'today' => $today,
        ]);
    }

    /**
     * Filterable appointment queue with inline prescription capture.
     */
    public function queue(Request $request): View
    {
        $doctor = $this->doctor();

        $date = $request->string('date')->trim()->toString() ?: today()->toDateString();
        $status = $request->string('status')->trim()->toString();

        $appointments = $doctor->appointments()
            ->with(['patient', 'clinic'])
            ->when(
                $date === 'all',
                fn ($q) => $q,
                fn ($q) => $q->whereDate('appointment_date', $date)
            )
            ->when(filled($status), fn ($q) => $q->where('status', $status))
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(20)
            ->withQueryString();

        return view('doctor.queue', [
            'doctor' => $doctor,
            'appointments' => $appointments,
            'date' => $date,
            'status' => $status,
            'statuses' => Appointment::statuses(),
        ]);
    }

    /**
     * Mark an appointment as `Completed`.
     */
    public function complete(Appointment $appointment): RedirectResponse
    {
        $this->authorizeAppointment($appointment);

        if ($appointment->status !== Appointment::STATUS_ACTIVE) {
            return back()->with('error', 'Only active appointments can be marked as completed.');
        }

        $appointment->update(['status' => Appointment::STATUS_COMPLETED]);

        return back()->with('success', 'Appointment for '.$appointment->patient->full_name.' marked as completed.');
    }

    /**
     * Mark an appointment as `Cancelled_by_Doctor`.
     */
    public function cancel(Appointment $appointment): RedirectResponse
    {
        $this->authorizeAppointment($appointment);

        if ($appointment->status !== Appointment::STATUS_ACTIVE) {
            return back()->with('error', 'Only active appointments can be cancelled.');
        }

        $appointment->update(['status' => Appointment::STATUS_CANCELLED_BY_DOCTOR]);

        return back()->with('success', 'Appointment for '.$appointment->patient->full_name.' cancelled.');
    }

    /**
     * Add or update the medical prescription of an appointment.
     */
    public function savePrescription(UpdatePrescriptionRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeAppointment($appointment);

        if ($appointment->status === Appointment::STATUS_CANCELLED_BY_PATIENT
            || $appointment->status === Appointment::STATUS_CANCELLED_BY_DOCTOR) {
            return back()->with('error', 'A prescription cannot be recorded for a cancelled appointment.');
        }

        $appointment->update([
            'disease' => $request->input('disease'),
            'allergies' => $request->input('allergies'),
            'prescription_details' => $request->input('prescription_details'),
        ]);

        return back()->with('success', 'Prescription saved for '.$appointment->patient->full_name.'.');
    }

    /* ---------------------------------------------------------------------
     | Internals
     | ------------------------------------------------------------------- */

    protected function doctor(): Doctor
    {
        /** @var Doctor $doctor */
        $doctor = Auth::guard('doctor')->user();

        abort_unless($doctor instanceof Doctor, 403);

        return $doctor;
    }

    protected function authorizeAppointment(Appointment $appointment): void
    {
        abort_unless($appointment->doctor_id === $this->doctor()->doctor_id, 403);
    }

    protected function currentSessionLog(): ?DoctorSessionLog
    {
        $logId = session('doctor_log_id');

        return $logId ? DoctorSessionLog::find($logId) : null;
    }
}

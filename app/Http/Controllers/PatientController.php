<?php

namespace App\Http\Controllers;

use App\Http\Requests\Patient\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Patient area: /patient
 */
class PatientController extends Controller
{
    /**
     * Appointment history plus a quick summary.
     */
    public function dashboard(): View
    {
        $patient = $this->patient();
        $today = today()->toDateString();

        $appointments = $patient->appointments()
            ->with(['doctor', 'clinic'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->get();

        $upcoming = $appointments
            ->filter(fn (Appointment $appointment) => $appointment->is_active
                && $appointment->appointment_date->toDateString() >= $today)
            ->sortBy(fn (Appointment $appointment) => $appointment->appointment_date->toDateString().' '.$appointment->appointment_time)
            ->values();

        $stats = [
            'total' => $appointments->count(),
            'upcoming' => $upcoming->count(),
            'completed' => $appointments->where('status', Appointment::STATUS_COMPLETED)->count(),
            'cancelled' => $appointments->whereIn('status', [
                Appointment::STATUS_CANCELLED_BY_PATIENT,
                Appointment::STATUS_CANCELLED_BY_DOCTOR,
            ])->count(),
            'prescriptions' => $appointments->filter(fn (Appointment $appointment) => $appointment->has_prescription)->count(),
        ];

        return view('patient.dashboard', [
            'patient' => $patient,
            'appointments' => $appointments,
            'upcoming' => $upcoming,
            'stats' => $stats,
            'today' => $today,
        ]);
    }

    /* ---------------------------------------------------------------------
     | Search & booking engine
     | ------------------------------------------------------------------- */

    /**
     * Step 1 - filter approved clinics by geographic area.
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
            ->paginate(9)
            ->withQueryString();

        return view('patient.clinics', [
            'area' => $area,
            'areas' => $areas,
            'clinics' => $clinics,
        ]);
    }

    /**
     * Step 2 - pick a doctor and one of the published time slots.
     */
    public function clinicDoctors(Clinic $clinic, Request $request): View
    {
        abort_unless($clinic->is_approved, 404, 'This clinic is not available for booking.');

        $date = $this->normaliseDate($request->string('date')->trim()->toString());

        $schedules = $clinic->schedules()
            ->with('doctor')
            ->onDate($date)
            ->ordered()
            ->get();

        $offers = $schedules->map(fn ($schedule) => [
            'schedule' => $schedule,
            'slots' => $schedule->slotAvailability(),
        ]);

        $availableDates = $clinic->schedules()
            ->onOrAfter(today()->toDateString())
            ->orderBy('schedule_date')
            ->pluck('schedule_date')
            ->map(fn ($value) => Carbon::parse($value)->toDateString())
            ->unique()
            ->take(30)
            ->values();

        $myAppointments = $this->patient()
            ->appointments()
            ->with('doctor')
            ->onDate($date)
            ->get();

        return view('patient.clinic_doctors', [
            'clinic' => $clinic,
            'date' => $date,
            'offers' => $offers,
            'availableDates' => $availableDates,
            'myAppointments' => $myAppointments,
        ]);
    }

    /**
     * Step 3 - book the appointment (status defaults to `Active`).
     */
    public function book(StoreAppointmentRequest $request): RedirectResponse
    {
        $patient = $this->patient();

        $clinic = Clinic::query()->findOrFail($request->integer('clinic_id'));

        if (! $clinic->is_approved) {
            return back()->with('error', 'This clinic is not available for booking.');
        }

        $doctorId = $request->integer('doctor_id');
        $date = $request->string('appointment_date')->toString();
        $time = $request->string('appointment_time')->toString();

        // The requested slot must fall inside a published schedule.
        $schedule = $clinic->schedules()
            ->forDoctor($doctorId)
            ->onDate($date)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>', $time)
            ->first();

        if (! $schedule) {
            return back()->with('error', 'That time slot is not part of any published schedule. Please pick another slot.');
        }

        // Capacity guard - never overbook a schedule.
        if ($schedule->bookedCount() >= (int) $schedule->patient_capacity) {
            return back()->with('error', 'This schedule has reached its full patient capacity ('.$schedule->patient_capacity.' patients). Please choose another doctor or date.');
        }

        // Someone may have taken the exact slot a moment ago.
        if ($schedule->slotConsumingAppointments()->where('appointment_time', $time)->exists()) {
            return back()->with('error', 'That time slot was just booked by another patient. Please choose another slot.');
        }

        // The same patient may not hold two active appointments with the same doctor on the same day.
        $alreadyBooked = $patient->appointments()
            ->active()
            ->where('doctor_id', $doctorId)
            ->where('clinic_id', $clinic->clinic_id)
            ->whereDate('appointment_date', $date)
            ->exists();

        if ($alreadyBooked) {
            return back()->with('error', 'You already have an active appointment with this doctor on '.$date.'.');
        }

        $appointment = Appointment::create([
            'patient_id' => $patient->patient_id,
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctorId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'status' => Appointment::STATUS_ACTIVE,
        ]);

        return redirect()
            ->route('patient.dashboard')
            ->with('success', 'Appointment booked with Dr. '.$appointment->doctor->full_name.' at '.$clinic->clinic_name.' on '.$appointment->appointment_date->format('d M Y').' at '.$appointment->formatted_time.'.');
    }

    /**
     * Cancel an active appointment (`Cancelled_by_Patient`).
     */
    public function cancel(Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->patient_id === $this->patient()->patient_id, 403);

        if ($appointment->status !== Appointment::STATUS_ACTIVE) {
            return back()->with('error', 'Only active appointments can be cancelled.');
        }

        $appointment->update(['status' => Appointment::STATUS_CANCELLED_BY_PATIENT]);

        return back()->with('success', 'Your appointment has been cancelled.');
    }

    /**
     * Printable prescription.
     */
    public function prescription(Appointment $appointment): View
    {
        abort_unless($appointment->patient_id === $this->patient()->patient_id, 403);

        $appointment->load(['patient', 'doctor', 'clinic']);

        return view('patient.prescription', compact('appointment'));
    }



    protected function patient(): Patient
    {
        /** @var Patient $patient */
        $patient = Auth::guard('patient')->user();

        abort_unless($patient instanceof Patient, 403);

        return $patient;
    }

    
    protected function normaliseDate(?string $date): string
    {
        if (! filled($date)) {
            return today()->toDateString();
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable $exception) {
            return today()->toDateString();
        }
    }
}

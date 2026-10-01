<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicSchedule;
use App\Models\Patient;

/**
 * Matches a patient request to a published ClinicSchedule and books it.
 *
 * Keeps HTTP concerns in PatientController and all business rules here:
 * clinic approval, schedule existence, time-slot membership, capacity,
 * duplicate-slot and duplicate doctor/day guards.
 */
class AppointmentBookingService
{
    /**
     * @return array{schedule: ClinicSchedule, appointment: Appointment}
     *
     * @throws AppointmentBookingException
     */
    public function book(Patient $patient, int $clinicId, int $doctorId, string $date, string $time): array
    {
        $clinic = Clinic::query()->find($clinicId);

        if (! $clinic instanceof Clinic || ! $clinic->is_approved) {
            throw new AppointmentBookingException('The selected clinic is not available for booking.');
        }

        $schedule = ClinicSchedule::query()
            ->forClinic($clinic->clinic_id)
            ->forDoctor($doctorId)
            ->onDate($date)
            ->first();

        if (! $schedule instanceof ClinicSchedule) {
            throw new AppointmentBookingException('That time slot is not part of any published schedule. Please pick another slot.');
        }

        if (! in_array($time, $schedule->slotTimes(), true)) {
            throw new AppointmentBookingException('That time slot is not part of any published schedule. Please pick another slot.');
        }

        if ($schedule->bookedCount() >= (int) $schedule->patient_capacity) {
            throw new AppointmentBookingException(
                'This schedule has reached its full patient capacity ('.$schedule->patient_capacity.' patients). Please choose another doctor or date.'
            );
        }

        if ($schedule->slotConsumingAppointments()->where('appointment_time', 'like', $time.'%')->exists()) {
            throw new AppointmentBookingException('That time slot was just booked by another patient. Please choose another slot.');
        }

        $alreadyBooked = $patient->appointments()
            ->active()
            ->where('doctor_id', $doctorId)
            ->where('clinic_id', $clinic->clinic_id)
            ->whereDate('appointment_date', $date)
            ->exists();

        if ($alreadyBooked) {
            throw new AppointmentBookingException('You already have an active appointment with this doctor on '.$date.'.');
        }

        $appointment = Appointment::create([
            'patient_id' => $patient->patient_id,
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctorId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'status' => Appointment::STATUS_ACTIVE,
        ]);

        return ['schedule' => $schedule, 'appointment' => $appointment->load(['doctor', 'clinic'])];
    }
}

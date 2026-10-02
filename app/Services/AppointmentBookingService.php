<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicSchedule;
use App\Models\Patient;
use App\Notifications\AppointmentNoShow;
use App\Support\Notifier;

class AppointmentBookingService
{
    /**
     * @return array{schedule: ClinicSchedule, appointment: Appointment}
     *
     * @throws AppointmentBookingException
     */
    public function book(Patient $patient, int $clinicId, int $doctorId, string $date, string $time, ?string $contactPhone = null): array
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

        // `contact_phone` / `checked_in_at` only exist after the attendance
        // migration has run. Never let a missing column become a 500 error.
        $hasContactPhone = Appointment::hasDatabaseColumn('contact_phone');
        $hasCheckedIn = Appointment::hasDatabaseColumn('checked_in_at');

        $payload = [
            'patient_id' => $patient->patient_id,
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctorId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'status' => Appointment::STATUS_ACTIVE,
        ];

        if ($hasContactPhone) {
            $payload['contact_phone'] = $contactPhone ?: $patient->contact;
        }

        $appointment = Appointment::create($payload);

        if ($hasContactPhone) {
            $appointment->contact_phone = $payload['contact_phone'];
        }

        if (! $hasCheckedIn) {
            unset($appointment->checked_in_at);
        }

        return ['schedule' => $schedule, 'appointment' => $appointment->load(['doctor', 'clinic'])];
    }

    /**
     * Mark arrival for an active appointment (admin / receptionist / clinic).
     */
    public function checkIn(Appointment $appointment): Appointment
    {
        if ($appointment->status !== Appointment::STATUS_ACTIVE) {
            throw new AppointmentBookingException('Only active appointments can be marked as arrived.');
        }

        if (! Appointment::hasDatabaseColumn('checked_in_at')) {
            throw new AppointmentBookingException(
                'Arrival tracking is unavailable until the pending database migration is run (`php artisan migrate --force`).'
            );
        }

        if (filled($appointment->checked_in_at)) {
            return $appointment;
        }

        $appointment->update(['checked_in_at' => now()]);

        return $appointment->fresh();
    }

    /**
     * Auto-cancel every active, not-checked-in appointment whose slot has passed.
     *
     * @return int Number of appointments cancelled.
     */
    public function cancelExpiredNoShows(?int $graceMinutes = null): int
    {
        if (! Appointment::hasDatabaseColumn('checked_in_at')) {
            return 0;
        }

        $graceMinutes ??= (int) config('appointments.no_show_grace_minutes', 0);
        $cutoff = now()->subMinutes(max(0, $graceMinutes));

        $expired = Appointment::query()
            ->active()
            ->notCheckedIn()
            ->with(['patient', 'doctor', 'clinic'])
            ->get()
            ->filter(fn (Appointment $appointment) => $appointment->starts_at !== null
                && $appointment->starts_at->lessThanOrEqualTo($cutoff));

        foreach ($expired as $appointment) {
            $appointment->update(['status' => Appointment::STATUS_NO_SHOW]);

            if ($appointment->patient) {
                Notifier::send($appointment->patient, new AppointmentNoShow($appointment));
            }
        }

        return $expired->count();
    }
}

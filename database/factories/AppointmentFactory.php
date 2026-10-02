<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'clinic_id' => Clinic::factory(),
            'doctor_id' => Doctor::factory(),
            'appointment_date' => today()->addDays($this->faker->numberBetween(0, 14))->toDateString(),
            'appointment_time' => $this->faker->randomElement(['09:00', '09:30', '10:00', '11:00', '16:00']),
            'status' => Appointment::STATUS_ACTIVE,
            'contact_phone' => $this->faker->numerify('9#########'),
            'checked_in_at' => null,
            'disease' => null,
            'allergies' => null,
            'prescription_details' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => Appointment::STATUS_COMPLETED,
            'disease' => $this->faker->randomElement(['Viral fever', 'Migraine', 'Hypertension']),
            'prescription_details' => 'Take rest and follow the prescribed dosage for 5 days.',
        ]);
    }
}

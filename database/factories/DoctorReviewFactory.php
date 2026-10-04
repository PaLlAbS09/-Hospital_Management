<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\DoctorReview;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorReview>
 */
class DoctorReviewFactory extends Factory
{
    protected $model = DoctorReview::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'patient_id' => Patient::factory(),
            'doctor_id' => fn (array $attributes) => Appointment::find($attributes['appointment_id'])?->doctor_id,
            'clinic_id' => fn (array $attributes) => Appointment::find($attributes['appointment_id'])?->clinic_id,
            'rating' => $this->faker->numberBetween(1, 5),
            'experience' => $this->faker->sentence(12),
            'is_public' => true,
        ];
    }

    /** A perfect score, used to crown the "best choice" badge. */
    public function perfect(): static
    {
        return $this->state(fn () => ['rating' => 5]);
    }

    public function private(): static
    {
        return $this->state(fn () => ['is_public' => false]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Clinic>
 */
class ClinicFactory extends Factory
{
    protected $model = Clinic::class;

    public function definition(): array
    {
        return [
            'clinic_name' => $this->faker->company().' Clinic',
            'area' => $this->faker->randomElement(['Bardhaman', 'Kolkata', 'Durgapur', 'Asansol', 'Howrah', 'Siliguri']),
            'email' => $this->faker->unique()->companyEmail(),
            'password' => Hash::make('password'),
            'contact_number' => $this->faker->numerify('9#########'),
            'status' => Clinic::STATUS_APPROVED,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => Clinic::STATUS_PENDING]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => Clinic::STATUS_REJECTED]);
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => Clinic::STATUS_APPROVED]);
    }
}

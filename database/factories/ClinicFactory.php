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
        $area = $this->faker->randomElement(['Bardhaman', 'Kolkata', 'Durgapur', 'Asansol', 'Howrah', 'Siliguri']);

        return [
            'clinic_name' => $this->faker->company().' Clinic',
            'area' => $area,
            'address' => $this->faker->streetAddress().', '.$area,
            'email' => $this->faker->unique()->companyEmail(),
            'password' => Hash::make('password'),
            'contact_number' => $this->faker->numerify('9#########'),
            'status' => Clinic::STATUS_APPROVED,
        ];
    }

    /** A clinic that never told us where it is, so it cannot be pinned. */
    public function withoutAddress(): static
    {
        return $this->state(fn () => [
            'address' => null,
            'latitude' => null,
            'longitude' => null,
        ]);
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

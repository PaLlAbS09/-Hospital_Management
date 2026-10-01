<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\ClinicSchedule;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClinicSchedule>
 */
class ClinicScheduleFactory extends Factory
{
    protected $model = ClinicSchedule::class;

    public function definition(): array
    {
        $start = $this->faker->randomElement(['09:00', '10:00', '14:00', '16:00']);

        return [
            'clinic_id' => Clinic::factory(),
            'doctor_id' => Doctor::factory(),
            'schedule_date' => today()->addDays($this->faker->numberBetween(0, 14))->toDateString(),
            'start_time' => $start,
            'end_time' => '17:00',
            'patient_capacity' => $this->faker->numberBetween(5, 20),
        ];
    }
}

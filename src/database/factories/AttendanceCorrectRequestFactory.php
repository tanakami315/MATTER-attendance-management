<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceCorrectRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'attendance_id' => Attendance::factory(),
            'clock_in' => $this->faker->time(),
            'clock_out' => $this->faker->time(),
            'comment' => $this->faker->sentence(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeFactory extends Factory
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
            'start_break' => $this->faker->time(),
            'end_break' => $this->faker->time(),
        ];
    }
}

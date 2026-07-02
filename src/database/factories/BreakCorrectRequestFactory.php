<?php

namespace Database\Factories;

use App\Models\AttendanceCorrectRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakCorrectRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'attendance_correct_request_id' => AttendanceCorrectRequest::factory(),
            'start_break' => $this->faker->time(),
            'end_break' => $this->faker->time(),
        ];
    }
}

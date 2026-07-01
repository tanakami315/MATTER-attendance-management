<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the before work status is displayed.
     *
     * @return void
     */
    public function test_before_work_status_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /**
     * Test that the working status is displayed.
     *
     * @return void
     */
    public function test_working_status_is_displayed(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-07-01 09:00:00');

        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /**
     * Test that the break status is displayed.
     *
     * @return void
     */
    public function test_break_status_is_displayed(): void
    {
        Carbon::setTestNow('2026-07-01 12:00:00');

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(3),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_break' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /**
     * Test that the after work status is displayed.
     *
     * @return void
     */
    public function test_after_work_status_is_displayed(): void
    {
        Carbon::setTestNow('2026-07-01 18:00:00');

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(9),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}

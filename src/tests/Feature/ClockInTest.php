<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the clock in button is functional.
     *
     * @return void
     */
    public function test_clock_in_button_is_functional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤');

        $response = $this->actingAs($user)
            ->post('/start_work');
        $response->assertRedirect('/attendance');

        $response = $this->actingAs($user)
            ->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /**
     * Test that clock in button is recorded only once per day.
     *
     * @return void
     */
    public function test_clock_in_button_is_recorded_only_once_per_day(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-07-01 18:00:00');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(9),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertDontSee('出勤');
    }

    /**
     * Test that clock in time is displayed correctly.
     *
     * @return void
     */
    public function test_clock_in_time_is_displayed_correctly(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-07-01 09:00:00');

        $response = $this->actingAs($user)
            ->post('/start_work');
        $response->assertRedirect('/attendance');

        $response = $this->actingAs($user)
            ->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('09:00');
    }
}

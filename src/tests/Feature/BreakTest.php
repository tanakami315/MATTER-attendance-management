<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the start break button is functional.
     *
     * @return void
     */
    public function test_start_break_button_is_functional(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-07-01 09:00:00');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩入');

        $response = $this->actingAs($user)
            ->post('/start_break');
        $response->assertRedirect('/attendance');

        $response = $this->actingAs($user)
            ->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /**
     * Test that the start break button is displayed multiple times a day.
     *
     * @return void
     */
    public function test_start_break_button_is_displayed_multiple_times_a_day(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-07-01 18:00:00');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(9),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_break' => now()->subHours(6),
            'end_break' => now()->subHours(5),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩入');
    }

    /**
     * Test that the end break button is functional.
     *
     * @return void
     */
    public function test_end_break_button_is_functional(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-07-01 13:00:00');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_break' => now()->subHours(1),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        $response = $this->actingAs($user)
            ->post('/end_break');
        $response->assertRedirect('/attendance');

        $response = $this->actingAs($user)
            ->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /**
     * Test that the end break button is displayed multiple times a day.
     *
     * @return void
     */
    public function test_end_break_button_is_displayed_multiple_times_a_day(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-07-01 19:00:00');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(10),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_break' => now()->subHours(7),
            'end_break' => now()->subHours(6),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_break' => now()->subHours(1),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩戻');
    }

    /**
     * Test that break time is displayed correctly.
     *
     * @return void
     */
    public function test_break_time_is_displayed_correctly(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-07-01 13:00:00');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(4),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_break' => now()->subHours(1),
        ]);

        $response = $this->actingAs($user)
            ->post('/end_break');
        $response->assertRedirect('/attendance');

        $response = $this->actingAs($user)
            ->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('1:00');
    }
}

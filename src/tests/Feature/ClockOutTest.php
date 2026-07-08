<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the clock out button is functional.
     *
     * @return void
     */
    public function test_clock_out_button_is_functional(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-07-01 18:00:00');

        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(9),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤');

        $response = $this->actingAs($user)
            ->post('/end_work');
        $response->assertRedirect('/attendance');

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    /**
     * Test that clock out time is displayed correctly.
     *
     * @return void
     */
    public function test_clock_out_time_is_displayed_correctly(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-07-01 18:00:00');

        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(9),
        ]);

        $response = $this->actingAs($user)
            ->post('/end_work');
        $response->assertRedirect('/attendance');

        $response = $this->actingAs($user)
            ->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('18:00');
    }
}

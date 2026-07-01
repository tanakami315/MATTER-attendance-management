<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the name is displayed correctly at detail page.
     *
     * @return void
     */
    public function test_name_is_displayed_correctly_at_detail_page(): void
    {
        $user = User::factory()->create([
            'name' => 'テスト秀一',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('テスト秀一');
    }

    /**
     * Test that the date is displayed correctly at detail page.
     *
     * @return void
     */
    public function test_date_is_displayed_correctly_at_detail_page(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-06-30 13:00:00');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y年'));
        $response->assertSee(now()->format('n月j日'));
    }

    /**
     * Test that the work records are displayed correctly at detail page.
     *
     * @return void
     */
    public function test_work_records_are_displayed_correctly_at_detail_page(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-06-30 18:15:00');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => Carbon::create(2026, 6, 30, 9, 0),
            'clock_out' => Carbon::create(2026, 6, 30, 18, 0),
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * Test that the break records are displayed correctly at detail page.
     *
     * @return void
     */
    public function test_break_records_are_displayed_correctly_at_detail_page(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-06-30 18:00:00');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_break' => now()->subHours(6),
            'end_break' => now()->subHours(5),
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
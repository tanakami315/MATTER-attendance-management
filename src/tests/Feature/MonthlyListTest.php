<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the monthly list is displayed correctly.
     *
     * @return void
     */
    public function test_monthly_list_is_displayed_correctly(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-06-30 13:00:00');

        Attendance::factory()
            ->count(15)
            ->sequence(
                fn ($sequence) => [
                    'user_id' => $user->id,
                    'date' => Carbon::create(2026, 6, $sequence->index + 1),
                    'clock_in' => Carbon::create(2026, 6, $sequence->index + 1, 9, 0),
                    'clock_out' => Carbon::create(2026, 6, $sequence->index + 1, 18, 0),
                ]
            )
            ->create();

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertSame(15, substr_count($html, '09:00'));
        $this->assertSame(15, substr_count($html, '18:00'));

    }

    /**
     * Test that the current month's attendance records are displayed.
     *
     * @return void
     */
    public function test_current_month_attendance_records_are_displayed(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-06-30 13:00:00');

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y/m'));

    }

    /**
     * Test that the previous month's attendance records are displayed.
     *
     * @return void
     */
    public function test_previous_month_attendance_records_are_displayed(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-06-30 13:00:00');

        Attendance::factory()
            ->count(17)
            ->sequence(
                fn ($sequence) => [
                    'user_id' => $user->id,
                    'date' => Carbon::create(2026, 5, $sequence->index + 1),
                    'clock_in' => Carbon::create(2026, 5, $sequence->index + 1, 9, 0),
                    'clock_out' => Carbon::create(2026, 5, $sequence->index + 1, 18, 0),
                ]
            )
            ->create();

        $response = $this->actingAs($user)
            ->get('/attendance/list?month=2026-05');

        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertSame(17, substr_count($html, '09:00'));
        $this->assertSame(17, substr_count($html, '18:00'));
        $response->assertSee(now()->subMonth()->format('Y/m'));

    }

    /**
     * Test that the next month's attendance records are displayed.
     *
     * @return void
     */
    public function test_next_month_attendance_records_are_displayed(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-06-30 13:00:00');

            $response = $this->actingAs($user)
            ->get('/attendance/list?month=2026-07');

        $response->assertStatus(200);

        $response->assertSee(now()->addMonth()->format('Y/m'));

    }

    /**
     * Test that the details of the attendance record are displayed.
     *
     * @return void
     */
    public function test_attendance_record_details_are_displayed(): void
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
        $response->assertSee('勤怠詳細');
        $response->assertSee(now()->format('Y年'));
        $response->assertSee(now()->format('n月j日'));
    }
}
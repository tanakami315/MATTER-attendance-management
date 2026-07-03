<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
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

        Attendance::factory()
            ->count(15)
            ->sequence(function ($sequence) use ($user) {
                $date = now()->copy()->day($sequence->index + 1);

                return [
                    'user_id' => $user->id,
                    'date' => $date,
                    'clock_in' => $date->copy()->setTime(9, 0),
                    'clock_out' => $date->copy()->setTime(18, 0),
                ];
            })
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

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(today()->format('Y/m'));
    }

    /**
     * Test that the previous month's attendance records are displayed.
     *
     * @return void
     */
    public function test_previous_month_attendance_records_are_displayed(): void
    {
        $user = User::factory()->create();

        $preMonth = today()->subMonth();

        Attendance::factory()
            ->count(17)
            ->sequence(function ($sequence) use ($user, $preMonth) {
                $date = $preMonth->copy()->day($sequence->index + 1);

                return [
                    'user_id' => $user->id,
                    'date' => $date,
                    'clock_in' => $date->copy()->setTime(9, 0),
                    'clock_out' => $date->copy()->setTime(18, 0),
                ];
            })
            ->create();

        $response = $this->actingAs($user)
            ->get('/attendance/list?month=' . $preMonth->format('Y-m'));

        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertSame(17, substr_count($html, '09:00'));
        $this->assertSame(17, substr_count($html, '18:00'));
        $response->assertSee($preMonth->format('Y/m'));

    }

    /**
     * Test that the next month's attendance records are displayed.
     *
     * @return void
     */
    public function test_next_month_attendance_records_are_displayed(): void
    {
        $user = User::factory()->create();

        $nextMonth = today()->addMonth();

        Attendance::factory()
            ->count(17)
            ->sequence(function ($sequence) use ($user, $nextMonth) {
                $date = $nextMonth->copy()->day($sequence->index + 1);

                return [
                    'user_id' => $user->id,
                    'date' => $date,
                    'clock_in' => $date->copy()->setTime(9, 0),
                    'clock_out' => $date->copy()->setTime(18, 0),
                ];
            })
            ->create();

        $response = $this->actingAs($user)
            ->get('/attendance/list?month=' . $nextMonth->format('Y-m'));

        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertSame(17, substr_count($html, '09:00'));
        $this->assertSame(17, substr_count($html, '18:00'));
        $response->assertSee($nextMonth->format('Y/m'));
    }

    /**
     * Test that the details of the attendance record are displayed.
     *
     * @return void
     */
    public function test_attendance_record_details_are_displayed(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee(today()->format('Y年'));
        $response->assertSee(today()->format('n月j日'));
    }
}
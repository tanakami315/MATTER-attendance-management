<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDailyListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the daily list is displayed correctly for administrators.
     *
     * @return void
     */
    public function test_daily_list_is_displayed_correctly(): void
    {
        $staffs = User::factory()->count(5)->create();

        $staffs->each(function ($staff) {
            $attendance = Attendance::factory()->create([
                'user_id' => $staff->id,
                'date' => today(),
                'clock_in' => today()->copy()->setTime(9, 0),
                'clock_out' => today()->copy()->setTime(18, 0)
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'start_break' => today()->copy()->setTime(12, 0),
                'end_break' => today()->copy()->setTime(13, 0),
            ]);
        });

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list');

        $response->assertStatus(200);

        $staffs->each(function ($staff) use ($response) {
            $response->assertSee($staff->name);
        });

        $html = $response->getContent();

        $this->assertSame(5, substr_count($html, '9:00'));
        $this->assertSame(5, substr_count($html, '18:00'));
        $this->assertSame(5, substr_count($html, '1:00'));
    }

    /**
     * Test that the today's attendance records are displayed for administrators.
     *
     * @return void
     */
    public function test_today_attendance_records_are_displayed_correctly(): void
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y年n月j日'));
    }

    /**
     * Test that the previous day's attendance records are displayed for administrators.
     *
     * @return void
     */
    public function test_previous_day_attendance_records_are_displayed(): void
    {
        $staffs = User::factory()->count(5)->create();

        $preDay = today()->subDay();

        $staffs->each(function ($staff) use ($preDay) {
            $attendance = Attendance::factory()->create([
                'user_id' => $staff->id,
                'date' => $preDay,
                'clock_in' => $preDay->copy()->setTime(9, 0),
                'clock_out' => $preDay->copy()->setTime(18, 0)
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'start_break' => $preDay->copy()->setTime(12, 0),
                'end_break' => $preDay->copy()->setTime(13, 0),
            ]);
        });

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?day=' . $preDay->format('Y-m-d'));

        $response->assertStatus(200);

        $staffs->each(function ($staff) use ($response) {
            $response->assertSee($staff->name);
        });

        $html = $response->getContent();

        $this->assertSame(5, substr_count($html, '9:00'));
        $this->assertSame(5, substr_count($html, '18:00'));
        $this->assertSame(5, substr_count($html, '1:00'));

        $response->assertSee($preDay->format('Y年n月j日'));
    }

    /**
     * Test that the next day's attendance records are displayed for administrators.
     *
     * @return void
     */
    public function test_next_day_attendance_records_are_displayed(): void
    {
        $staffs = User::factory()->count(5)->create();

        $nextDay = today()->addDay();

        $staffs->each(function ($staff) use ($nextDay) {
            $attendance = Attendance::factory()->create([
                'user_id' => $staff->id,
                'date' => $nextDay,
                'clock_in' => $nextDay->copy()->setTime(9, 0),
                'clock_out' => $nextDay->copy()->setTime(18, 0)
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'start_break' => $nextDay->copy()->setTime(12, 0),
                'end_break' => $nextDay->copy()->setTime(13, 0),
            ]);
        });

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?day=' . $nextDay->format('Y-m-d'));

        $response->assertStatus(200);

        $staffs->each(function ($staff) use ($response) {
            $response->assertSee($staff->name);
        });
        $html = $response->getContent();

        $this->assertSame(5, substr_count($html, '9:00'));
        $this->assertSame(5, substr_count($html, '18:00'));
        $this->assertSame(5, substr_count($html, '1:00'));

        $response->assertSee($nextDay->format('Y年n月j日'));
    }
}
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the staff list is displayed correctly.
     *
     * @return void
     */
    public function test_staff_list_is_displayed_correctly(): void
    {
        $staffs = User::factory()->count(5)->create();

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/staff/list');

        $response->assertStatus(200);

        $staffs->each(function ($staff) use ($response) {
            $response->assertSee($staff->name);
            $response->assertSee($staff->email);
        });
    }

    /**
     * Test that the monthly list is displayed correctly.
     *
     * @return void
     */
    public function test_monthly_list_is_displayed_correctly(): void
    {
        $staff = User::factory()->create();

        Attendance::factory()
            ->count(15)
            ->sequence(function ($sequence) use ($staff) {
                $date = now()->copy()->day($sequence->index + 1);

                return [
                    'user_id' => $staff->id,
                    'date' => $date,
                    'clock_in' => $date->copy()->setTime(9, 0),
                    'clock_out' => $date->copy()->setTime(18, 0),
                ];
            })
            ->create();

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);
    
        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $staff->id);

        $response->assertStatus(200);

        $html = $response->getContent();

        $response->assertSee($staff->name);
        $response->assertSee(today()->format('Y/m'));
        $this->assertSame(15, substr_count($html, '09:00'));
        $this->assertSame(15, substr_count($html, '18:00'));

    }

    /**
     * Test that the previous month's attendance records are displayed.
     *
     * @return void
     */
    public function test_previous_month_attendance_records_are_displayed(): void
    {
        $staff = User::factory()->create();

        $preMonth = today()->subMonth();

        Attendance::factory()
            ->count(17)
            ->sequence(function ($sequence) use ($staff, $preMonth) {
                $date = $preMonth->copy()->day($sequence->index + 1);

                return [
                    'user_id' => $staff->id,
                    'date' => $date,
                    'clock_in' => $date->copy()->setTime(9, 0),
                    'clock_out' => $date->copy()->setTime(18, 0),
                ];
            })
            ->create();

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $staff->id . '?month=' . $preMonth->format('Y-m'));

        $response->assertStatus(200);

        $html = $response->getContent();
        $response->assertSee($staff->name);
        $response->assertSee($preMonth->format('Y/m'));
        $this->assertSame(17, substr_count($html, '09:00'));
        $this->assertSame(17, substr_count($html, '18:00'));

    }

    /**
     * Test that the next month's attendance records are displayed.
     *
     * @return void
     */
    public function test_next_month_attendance_records_are_displayed(): void
    {
        $staff = User::factory()->create();

        $nextMonth = today()->addMonth();

        Attendance::factory()
            ->count(17)
            ->sequence(function ($sequence) use ($staff, $nextMonth) {
                $date = $nextMonth->copy()->day($sequence->index + 1);

                return [
                    'user_id' => $staff->id,
                    'date' => $date,
                    'clock_in' => $date->copy()->setTime(9, 0),
                    'clock_out' => $date->copy()->setTime(18, 0),
                ];
            })
            ->create();

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $staff->id . '?month=' . $nextMonth->format('Y-m'));

        $response->assertStatus(200);

        $html = $response->getContent();
        $response->assertSee($staff->name);
        $response->assertSee($nextMonth->format('Y/m'));
        $this->assertSame(17, substr_count($html, '09:00'));
        $this->assertSame(17, substr_count($html, '18:00'));
    }

    /**
     * Test that the details of the attendance record are displayed.
     *
     * @return void
     */
    public function test_attendance_record_details_are_displayed(): void
    {
        $staff = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $staff->id,
            'date' => today(),
        ]);

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($staff->name);
        $response->assertSee(today()->format('Y年'));
        $response->assertSee(today()->format('n月j日'));
    }
}
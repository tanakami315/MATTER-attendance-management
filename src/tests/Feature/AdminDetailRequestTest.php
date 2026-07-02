<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDetailRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that pending correction requests are displayed correctly.
     *
     * @return void
     */
    public function test_pending_correction_requests_are_displayed_correctly(): void
    {
        $staffs = User::factory()->count(5)->create();

        $staffs->each(function ($staff) {
            $attendance = Attendance::factory()->create([
                'user_id' => $staff->id,
            ]);

            AttendanceCorrectRequest::factory()->create([
                'attendance_id' => $attendance->id,
                'status' => 0,
                'comment' => '打刻忘れのため',
            ]);
        });

        $correctedAttendance = Attendance::factory()->create([
                'user_id' => $staffs->random()->id,
        ]);

        AttendanceCorrectRequest::factory()->create([
                'attendance_id' => $correctedAttendance->id,
                'status' => 1,
                'comment' => '電車遅延のため',
        ]);

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get('/stamp_correction_request/list/?tab=pending');

        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertSame(5, substr_count($html, '打刻忘れのため'));
        $response->assertDontSee('電車遅延のため');
    }

    /**
     * Test that approved correction requests are displayed correctly.
     *
     * @return void
     */
    public function test_approved_correction_requests_are_displayed_correctly(): void
    {
        $staffs = User::factory()->count(5)->create();

        $staffs->each(function ($staff) {
            $correctedAttendance = Attendance::factory()->create([
                'user_id' => $staff->id,
            ]);

            AttendanceCorrectRequest::factory()->create([
                'attendance_id' => $correctedAttendance->id,
                'status' => 1,
                'comment' => '打刻忘れのため',
            ]);
        });

        $attendance = Attendance::factory()->create([
                'user_id' => $staffs->random()->id,
        ]);

        AttendanceCorrectRequest::factory()->create([
                'attendance_id' => $attendance->id,
                'status' => 0,
                'comment' => '電車遅延のため',
        ]);

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get('/stamp_correction_request/list/?tab=approved');

        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertSame(5, substr_count($html, '打刻忘れのため'));
        $response->assertDontSee('電車遅延のため');
    }


    /**
     * Test that an administrator can view the attendance correction request details.
     *
     * @return void
     */
    public function test_admin_can_view_attendance_correction_request_details(): void
    {
        $staff = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $staff->id,
            'date' => today(),
        ]);

        $attendance_correct_request = AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => today()->copy()->setTime(9, 0),
            'clock_out' => today()->copy()->setTime(18, 0),
            'comment' => '打刻忘れのため',
        ]);

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get("/stamp_correction_request/approve/{$attendance_correct_request->id}");

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($staff->name);
        $response->assertSee(today()->format('Y年'));
        $response->assertSee(today()->format('n月j日'));
        $response->assertSee('9:00');
        $response->assertSee('18:00');
        $response->assertSee('打刻忘れのため');
    }

    /**
     * Test that an administrator can approve an correction request successfully.
     *
     * @return void
     */
    public function test_admin_can_approve_correction_request(): void
    {
        $staff = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $staff->id,
        ]);

        $attendance_correct_request = AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'comment' => '電車遅延のため、出勤時間が遅れました。',
        ]);

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $this->actingAs($admin)
            ->post("/admin/approve/{$attendance_correct_request->id}", [
                'comment' => $attendance_correct_request->comment,
            ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'comment' => "電車遅延のため、出勤時間が遅れました。",
        ]);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'id' => $attendance_correct_request->id,
            'status' => 1,
        ]);

    }
}
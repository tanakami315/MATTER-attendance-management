<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorrectRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the clock out time must be after the clock in time.
     *
     * @return void
     */
    public function test_clock_out_time_must_be_after_clock_in_time(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->post("/stamp_correction_request/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '08:00',
                'comment' => '退勤時間が出勤時間より前のテスト',
            ]);

        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /**
     * Test that the start break time must be before the clock out time.
     *
     * @return void
     */
    public function test_start_break_time_must_be_before_clock_out_time(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->post("/stamp_correction_request/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '10:00',
                'start_break' => ['12:00'],
                'end_break' => ['13:00'],
                'comment' => '休憩時間が退勤時間より後のテスト',
            ]);

        $response->assertSessionHasErrors([
            'start_break.0' => '休憩時間が不適切な値です'
        ]);
    }

    /**
     * Test that the end break time must be before the clock out time.
     *
     * @return void
     */
    public function test_end_break_time_must_be_before_clock_out_time(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->post("/stamp_correction_request/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'start_break' => ['12:00'],
                'end_break' => ['19:00'],
                'comment' => '休憩時間が退勤時間より後のテスト',
            ]);

        $response->assertSessionHasErrors([
            'end_break.0' => '休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /**
     * Test that the comment must be required.
     *
     * @return void
     */
    public function test_comment_must_be_required(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->post("/stamp_correction_request/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'start_break' => ['12:00'],
                'end_break' => ['13:00'],
                'comment' => '',
            ]);

        $response->assertSessionHasErrors([
            'comment' => '備考を記入してください'
        ]);
    }

    /**
     * Test that a user can create an correction request successfully.
     *
     * @return void
     */
    public function test_user_can_create_correction_request(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post("/stamp_correction_request/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'start_break' => ['12:00'],
                'end_break' => ['13:00'],
                'comment' => '電車遅延のため、出勤時間が遅れました。',
            ]);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'attendance_id' => $attendance->id,
            'comment' => '電車遅延のため、出勤時間が遅れました。',
        ]);

        $attendanceCorrectRequest = AttendanceCorrectRequest::first();

        $this->assertDatabaseHas('break_correct_requests', [
            'attendance_correct_request_id' => $attendanceCorrectRequest->id,
        ]);
    }


    /**
     * Test that pending correction requests are displayed correctly.
     *
     * @return void
     */
    public function test_pending_correction_requests_are_displayed_correctly(): void
    {
        $user = User::factory()->create();

        $attendances = Attendance::factory()->count(5)->create([
            'user_id' => $user->id,
        ]);

        $attendances->each(function ($attendance) {
            AttendanceCorrectRequest::factory()->create([
                'attendance_id' => $attendance->id,
                'status' => 0,
                'comment' => '打刻忘れのため',
            ]);
        });

        $correctedAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $correctedAttendance->id,
            'status' => '1',
            'comment' => '電車遅延のため',
        ]);

        $response = $this->actingAs($user)
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
        $user = User::factory()->create();

        $correctedAttendances = Attendance::factory()->count(5)->create([
            'user_id' => $user->id,
        ]);

        $correctedAttendances->each(function ($attendance) {
            AttendanceCorrectRequest::factory()->create([
                'attendance_id' => $attendance->id,
                'status' => 1,
                'comment' => '打刻忘れのため',
            ]);
        });

        $attendances = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendances->id,
            'status' => '0',
            'comment' => '電車遅延のため',
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list/?tab=approved');

        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertSame(5, substr_count($html, '打刻忘れのため'));
        $response->assertDontSee('電車遅延のため');
    }

    /**
     * Test that a user can view the attendance record details from the correction request page.
     *
     * @return void
     */
    public function test_user_can_view_attendance_record_details_from_correction_request_page(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'comment' => '打刻忘れのため',
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('打刻忘れのため');
    }
}
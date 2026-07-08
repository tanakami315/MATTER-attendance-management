<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the attendance record is displayed correctly at detail page for administrators.
     *
     * @return void
     */
    public function test_attendance_record_is_displayed_correctly_at_detail_page(): void
    {
        $staff = User::factory()->create([
            'name' => 'テスト秀一',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $staff->id,
            'date' => today(),
            'clock_in' => today()->copy()->setTime(9, 0),
            'clock_out' => today()->copy()->setTime(18, 0),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_break' => today()->copy()->setTime(12, 0),
            'end_break' => today()->copy()->setTime(13, 0),
        ]);

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('テスト秀一');
        $response->assertSee( today()->format('Y年'));
        $response->assertSee(today()->format('n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    /**
     * Test that the clock out time must be after the clock in time.
     *
     * @return void
     */
    public function test_clock_out_time_must_be_after_clock_in_time(): void
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($admin)
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
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($admin)
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
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($admin)
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
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($admin)
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
}
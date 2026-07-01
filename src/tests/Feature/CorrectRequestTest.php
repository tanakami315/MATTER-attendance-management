<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakTime;
use Carbon\Carbon;
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

}
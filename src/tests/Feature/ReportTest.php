<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a guest cannot view attendance reports.
     *
     * @return void
     */
    public function test_guest_cannot_view_attendance_reports(): void
    {
        $response = $this->get('/attendance/report');
        $response->assertRedirect('/login');
    }

    /**
     * Test that attendance reports can be displayed correctly.
     *
     * @return void
     */
    public function test_attendance_reports_can_be_displayed(): void
    {
        $user = User::factory()->create();

        $attendances = Attendance::factory()
            ->count(19)
            ->sequence(function ($sequence) use ($user) {
                $date = today()->copy()->day($sequence->index + 1);

                return [
                    'user_id' => $user->id,
                    'date' => $date,
                    'clock_in' => $date->copy()->setTime(9, 0),
                    'clock_out' => $date->copy()->setTime(18, 0),
                ];
            })
            ->create();

        $attendances->each(function ($attendance) {
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'start_break' => $attendance->date->copy()->setTime(12, 0),
                'end_break' => $attendance->date->copy()->setTime(13, 0),
                ]);
            }
        );

        $overworkAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today()->copy()->day(20),
            'clock_in'=> today()->copy()->day(20)->setTime(9,0),
            'clock_out'=> today()->copy()->day(20)->setTime(20,0),
        ]);

        BreakTime::create([
            'attendance_id' => $overworkAttendance->id,
            'start_break' => today()->copy()->day(20)->setTime(12, 0),
            'end_break' => today()  ->copy()->day(20)->setTime(13, 0),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/report');

        $response->assertStatus(200);
        $response->assertSee('162h 0m');
        $response->assertSee('2h 0m');
        $response->assertSee('8h 6m');
    }

    /**
     * Test that attendance reports can be displayed correctly when attendance record is not exist.
     *
     * @return void
     */
    public function test_attendance_reports_can_be_displayed_when_attendance_record_is_not_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/attendance/report');

        $response->assertStatus(200);
        $html = $response->getContent();
        $this->assertSame(15, substr_count($html, '0h 0m'));
    }

}

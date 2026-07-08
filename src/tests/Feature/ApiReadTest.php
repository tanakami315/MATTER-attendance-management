<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakTime;
use App\Models\BreakCorrectRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiReadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that attendance records can be fetched as JSON.
     *
     * @return void
     */
    public function test_attendance_records_can_be_fetched_as_json(): void
    {
        $users = User::factory()->count(2)->create();

        $users->each(function ($user) {
            Attendance::factory()
                ->count(100)
                ->sequence(function ($sequence) use ($user) {
                    $date = today()->subDays($sequence->index);

                    return [
                        'user_id' => $user->id,
                        'date' => $date,
                        'clock_in' => $date->copy()->setTime(9, 0),
                        'clock_out' => $date->copy()->setTime(18, 0),
                    ];
                })
                ->create();
        });

        $response =
            $this->getJson('/api/v1/attendance-records');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
    }

    /**
     * Test that attendance record details can be fetched as JSON.
     *
     * @return void
     */
    public function test_attendance_record_details_can_be_fetched_as_json(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
        ]);

        $attendance_correct_request = AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
        ]);

        BreakCorrectRequest::factory()->create([
            'attendance_correct_request_id' => $attendance_correct_request->id,
        ]);

        $response =
            $this->getJson('/api/v1/attendance-records/' . $attendance->id);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'user',
                'date',
                'clock_in',
                'clock_out',
                'work_time',
                'break_time',
                'comment',
                'breakTimes' => [
                    '*' => [
                        'start_break',
                        'end_break',
                    ],
                ],
                'attendanceCorrectRequests' => [
                    '*' => [
                        'clock_in',
                        'clock_out',
                        'comment',
                        'status',
                        'breakCorrectRequests' => [
                            '*' => [
                                'start_break',
                                'end_break',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test that non-existent record cannot be fetched as JSON.
     *
     * @return void
     */
    public function test_non_existent_record_cannot_be_fetched_as_json(): void
    {
        $response =
            $this->getJson('/api/v1/attendance-records/99999' );

        $response->assertStatus(404);

        $response->assertJson([
            'error' => '勤怠情報が見つかりませんでした。'
        ]);
    }
}

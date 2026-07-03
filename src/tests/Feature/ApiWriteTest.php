<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiWriteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that attendance records can be created as JSON.
     *
     * @return void
     */
    public function test_attendance_records_can_be_created_as_json(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/attendance-records', [
                'date' => '2026-07-01',
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'comment' => 'APIから作成',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => '2026-07-01',
            'clock_in' => '2026-07-01 09:00:00',
            'clock_out' => '2026-07-01 18:00:00',
            'comment' => 'APIから作成',
        ]);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
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
            ],
        ]);
    }

    /**
     * Test that attendance record cannot be created without clock in.
     *
     * @return void
     */
    public function test_attendance_record_cannot_be_created_without_clock_in(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/attendance-records', [
                'date' => '2026-07-01',
                'clock_in' => '',
            ]);

        $response->assertStatus(422);

        $response->assertJson([
            'errors' => [
                'clock_in' => [
                    '必須未入力：出勤時刻は必須です。',
                ],
            ],
        ]);
    }

    /**
     * Test that attendance records can be updated as JSON.
     *
     * @return void
     */
    public function test_attendance_records_can_be_updated_as_json(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/attendance-records/' . $attendance->id, [
                'date' => '2026-07-01',
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'comment' => 'APIから更新',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => '2026-07-01',
            'clock_in' => '2026-07-01 09:00:00',
            'clock_out' => '2026-07-01 18:00:00',
            'comment' => 'APIから更新',
        ]);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
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
            ],
        ]);
    }

    /**
     * Test that non-existent record cannot be updated as JSON.
     *
     * @return void
     */
    public function test_non_existent_record_cannot_be_updated_as_json(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/attendance-records/99999', [
                'date' => '2026-07-01',
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'comment' => 'APIから更新',
            ]);

        $response->assertStatus(404);

        $response->assertJson([
            'error' => '勤怠情報が見つかりませんでした。'
        ]);
    }

    /**
     * Test that attendance records can be deleted as JSON.
     *
     * @return void
     */
    public function test_attendance_records_can_be_deleted_as_json(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/v1/attendance-records/' . $attendance->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
        ]);
    }

    /**
     * Test that non-existent record cannot be deleted as JSON.
     *
     * @return void
     */
    public function test_non_existent_record_cannot_be_deleted_as_json(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson('/api/v1/attendance-records/99999');

        $response->assertStatus(404);

        $response->assertJson([
            'error' => '勤怠情報が見つかりませんでした。'
        ]);
    }

}

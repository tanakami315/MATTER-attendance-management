<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SanctumTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that attendance records cannot be created as JSON without authentication.
     *
     * @return void
     */
    public function test_attendance_records_cannot_be_created_without_authentication(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/attendance-records', [
                'date' => '2026-07-01',
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'comment' => 'APIから作成',
            ]);

        $response->assertStatus(401);

        $response->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    /**
     * Test that attendance records cannot be updated as JSON without authentication.
     *
     * @return void
     */
    public function test_attendance_records_cannot_be_updated_without_authentication(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->patchJson('/api/v1/attendance-records/' . $attendance->id, [
                'date' => '2026-07-01',
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'comment' => 'APIから作成',
        ]);

        $response->assertStatus(401);

        $response->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    /**
     * Test that attendance records cannot be deleted as JSON without authentication.
     *
     * @return void
     */
    public function test_attendance_records_cannot_be_deleted_without_authentication(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson('/api/v1/attendance-records/' . $attendance->id);

        $response->assertStatus(401);

        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    /**
     * Test that attendance records can be updated as JSON with authentication.
     *
     * @return void
     */
    public function test_attendance_records_can_be_updated_as_json(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/attendance-records/' . $attendance->id, [
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
     * Test that attendance records can be deleted as JSON with authentication.
     *
     * @return void
     */
    public function test_attendance_records_can_be_deleted_as_json(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this
            ->deleteJson('/api/v1/attendance-records/' . $attendance->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
        ]);
    }

    /**
     * Test that attendance records cannot be updated as JSON without authorization.
     *
     * @return void
     */
    public function test_attendance_records_cannot_be_updated_without_authorization(): void
    {
        $user1 = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user1->id,
        ]);

        $user2 = User::factory()->create();

        Sanctum::actingAs($user2);

        $response = $this->patchJson('/api/v1/attendance-records/' . $attendance->id, [
                'date' => '2026-07-01',
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'comment' => 'APIから作成',
            ]);

        $response->assertStatus(403);

        $response->assertJson([
                    'error' => 'この操作を実行する権限がありません。'
                ]);
    }

    /**
     * Test that attendance records cannot be deleted as JSON without authorization.
     *
     * @return void
     */
    public function test_attendance_records_cannot_be_deleted_without_authorization(): void
    {
        $user1 = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user1->id,
        ]);

        $user2 = User::factory()->create();

        Sanctum::actingAs($user2);

        $response = $this->deleteJson('/api/v1/attendance-records/' . $attendance->id);

        $response->assertStatus(403);

        $response->assertJson([
                    'error' => 'この操作を実行する権限がありません。'
                ]);
    }
}

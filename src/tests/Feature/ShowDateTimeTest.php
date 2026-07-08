<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowDateTimeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the current date and time are displayed.
     *
     * @return void
     */
    public function test_current_date_and_time_are_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('id="current-date"', false);
        $response->assertSee('id="current-time"', false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

class VerifyEmailTest extends \Tests\TestCase
{
    use RefreshDatabase;

    /**
     * Test that email verification notification is sent.
     *
     * @return void
     */
    public function test_email_verification_notification_is_sent(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $user->notify(new VerifyEmail);

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    /**
     * Test that verification button_has mailhog link.
     *
     * @return void
     */
    public function test_verification_button_has_mailhog_link()
    {
        $user = User::factory()->unverified()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user)
            ->get('/email/verify');

        $response->assertStatus(200);

        $response->assertSee('認証はこちらから');
    }

    /**
     * Test that test verified user is redirected to stamp attendance page.
     *
     * @return void
     */
    public function test_verified_user_redirects_to_stamp_attendance_page()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/redirect-after-login');

        $response->assertRedirect('/attendance');
    }
}
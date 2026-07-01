<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that name is required for registration.
     *
     * @return void
     */
    public function test_name_is_required(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください'
        ]);
    }

    /**
     * Test that email is required for registration.
     *
     * @return void
     */
    public function test_email_is_required(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト秀一',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
    }

    /**
     * Test that password must be at least 8 characters for registration.
     *
     * @return void
     */
    public function test_password_must_be_at_least_8_characters(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト秀一',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください'
        ]);
    }

    /**
     * Test that password_confirmation must match the password for registration.
     *
     * @return void
     */
    public function test_password_confirmation_must_match(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト秀一',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません'
        ]);
    }

    /**
     * Test that password is required for registration.
     *
     * @return void
     */
    public function test_password_is_required(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト秀一',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
    }

    /**
     * Test that a user can register successfully.
     *
     * @return void
     */
    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト秀一',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/redirect-after-login');

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }
}
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_メールアドレスが未入力の場合はバリデーションエラーになる()
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertEquals(
            'メールアドレスを入力してください',
            session('errors')->first('email')
        );
    }

    public function test_パスワードが未入力の場合はバリデーションエラーになる()
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertEquals(
            'パスワードを入力してください',
            session('errors')->first('password')
        );
    }

    public function test_入力情報が間違っている場合はバリデーションエラーになる()
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertEquals(
            'ログイン情報が登録されていません。',
            session('errors')->first('email')
        );
    }

    public function test_正しい情報でログインできる()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/attendance');
        $this->assertAuthenticatedAs($user);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_パスワードが未入力の場合はバリデーションエラーになる()
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertEquals(
            'パスワードを入力してください',
            session('errors')->first('password')
        );
    }

    public function test_パスワードが短すぎる場合はバリデーションエラーになる()
    {
        $response = $this->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'short7',
            'password_confirmation' => 'short7',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertEquals(
            'パスワードは8文字以上で入力してください',
            session('errors')->first('password')
        );
    }

    public function test_パスワード確認用が一致しない場合はバリデーションエラーになる()
    {
        $response = $this->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrongpass123',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertEquals(
            'パスワードと一致しません',
            session('errors')->first('password')
        );
    }

    public function test_正しい入力で登録するとログイン画面にリダイレクトされる()
    {
        $response = $this->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 成功後、ログイン画面またはプロフィール作成画面へ遷移
        $response->assertRedirect('/');

        // DBにユーザーが保存されているか確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }
}

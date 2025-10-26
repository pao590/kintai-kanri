<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AdminLoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_メールアドレスが未入力の場合はバリデーションメッセージが表示される()
    {
        $response = $this->from(route('admin.login'))->post(route('admin.login'), [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertEquals(
            'メールアドレスを入力してください',
            session('errors')->first('email')
        );
    }

    /**
     * パスワード未入力時のバリデーションテスト
     *
     * @return void
     */
    public function test_パスワードが未入力の場合はバリデーションメッセージが表示される()
    {
        $response = $this->from(route('admin.login'))->post(route('admin.login'), [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertEquals(
            'パスワードを入力してください',
            session('errors')->first('password')
        );
    }

    /**
     * 登録情報と一致しない場合のバリデーションテスト
     *
     * @return void
     */
    public function test_登録内容と一致しない場合はバリデーションメッセージが表示される()
    {
        $response = $this->from(route('admin.login'))->post(route('admin.login'), [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertEquals(
            'ログイン情報が登録されていません。',
            session('errors')->first('email')
        );
    }

    /**
     * 正しい情報でログインできることの確認
     *
     * @return void
     */
    public function test_正しい情報でログインできる()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 1,
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.attendance.list'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }
}

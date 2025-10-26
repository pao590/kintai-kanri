<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;


class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_現在日時が画面上に正しい形式で表示される()
    {
        // テスト用日時を固定
        Carbon::setTestNow($now = Carbon::now());

        // ユーザー作成
        $user = User::factory()->create();

        // 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => $now,
        ]);

        // ログイン済みにする
        $this->actingAs($user);

        // 勤怠打刻画面にアクセス
        $response = $this->get(route('attendance.index')); // Bladeでのルート名に合わせる
        $response->assertStatus(200);

        // Bladeでの表示形式に合わせる
        $expected = $attendance->clock_in->format('Y年n月j日');
        $response->assertSee($expected);
    }
}

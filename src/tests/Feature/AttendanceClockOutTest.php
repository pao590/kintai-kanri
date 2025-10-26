<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_退勤ボタンを押すとステータスが退勤済になる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤処理
        $this->post(route('attendance.clock_in'));

        // 退勤処理
        $this->post(route('attendance.store'), ['action' => 'end_work']);

        $attendance = Attendance::where('user_id', $user->id)->first();

        $this->assertEquals('after_work', $attendance->status);

        $response = $this->get(route('attendance.index'));
        $response->assertSee('退勤済');
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤処理
        $this->post(route('attendance.clock_in'));

        // 退勤処理
        $this->post(route('attendance.store'), ['action' => 'end_work']);

        $attendance = Attendance::where('user_id', $user->id)->first();

        $this->assertNotNull($attendance->clock_out, '退勤時刻が記録されていません');

        $response = $this->get(route('attendance.list'));
        $response->assertSee($attendance->clock_out->format('H:i'));
    }
}

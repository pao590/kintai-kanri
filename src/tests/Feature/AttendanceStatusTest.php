<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_勤務外ステータスが表示される()
    {
        $user = User::factory()->create();

        // 当日の勤怠がない状態 → before_work
        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /** 出勤中ステータスが表示される */
    public function test_出勤中ステータスが表示される()
    {
        $user = User::factory()->create();
        Attendance::factory()->working()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /** 休憩中ステータスが表示される */
    public function test_休憩中ステータスが表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->working()->create(['user_id' => $user->id]);

        // 未終了の休憩レコードを追加
        $attendance->rests()->create([
            'rest_start' => Carbon::now(),
            'rest_end' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /** 退勤済ステータスが表示される */
    public function test_退勤済ステータスが表示される()
    {
        $user = User::factory()->create();
        Attendance::factory()->afterWork()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}

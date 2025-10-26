<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_出勤ボタンを押すとステータスが勤務中になる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('attendance.clock_in'));
        $response->assertStatus(302);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'working',
        ]);

        $response = $this->get(route('attendance.index'));
        $response->assertSee('出勤中');
    }

    public function test_一度出勤したユーザーは再度出勤できない()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now(),
            'status' => 'working',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));
        $response->assertDontSee('value="start_work"');
    }

    public function test_出勤時刻が勤怠一覧画面に表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('attendance.clock_in'));

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('clock_in', now()->toDateString())
            ->first();

        $this->assertNotNull($attendance, '出勤レコードが作成されていません');

        $response = $this->get(route('attendance.list'));
        $response->assertSee($attendance->clock_in->format('H:i'));
    }
}


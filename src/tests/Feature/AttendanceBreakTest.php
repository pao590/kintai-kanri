<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_休憩入ボタンを押すとステータスが休憩中になる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('attendance.clock_in'));

        $this->post(route('attendance.store'), ['action' => 'rest_start']);

        $attendance = Attendance::where('user_id', $user->id)->first();

        $this->assertEquals('break', $attendance->status);

        $response = $this->get(route('attendance.index'));
        $response->assertSee('休憩中');
    }

    public function test_休憩は一日に何回でもできる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('attendance.clock_in'));

        $this->post(route('attendance.store'), ['action' => 'rest_start']);
        $this->post(route('attendance.store'), ['action' => 'rest_end']);

        $this->post(route('attendance.store'), ['action' => 'rest_start']);

        $attendance = Attendance::where('user_id', $user->id)->first();
        $this->assertEquals('break', $attendance->status);

        $response = $this->get(route('attendance.index'));
        $response->assertSee('休憩中');
    }

    public function test_休憩戻ボタンを押すとステータスが出勤中になる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('attendance.clock_in'));
        $this->post(route('attendance.store'), ['action' => 'rest_start']);

        $this->post(route('attendance.store'), ['action' => 'rest_end']);

        $attendance = Attendance::where('user_id', $user->id)->first();
        $this->assertEquals('working', $attendance->status);

        $response = $this->get(route('attendance.index'));
        $response->assertSee('出勤中');
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('attendance.clock_in'));

        $this->post(route('attendance.store'), ['action' => 'rest_start']);
        $this->post(route('attendance.store'), ['action' => 'rest_end']);

        $this->post(route('attendance.store'), ['action' => 'rest_start']);
        $this->post(route('attendance.store'), ['action' => 'rest_end']);

        $attendance = Attendance::where('user_id', $user->id)->first();
        $this->assertEquals('working', $attendance->status);

        $response = $this->get(route('attendance.index'));
        $response->assertSee('出勤中');
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('attendance.clock_in'));
        $this->post(route('attendance.store'), ['action' => 'rest_start']);
        $this->post(route('attendance.store'), ['action' => 'rest_end']);

        $attendance = Attendance::where('user_id', $user->id)->with('rests')->first();

        $this->assertNotNull($attendance->rests->first()->rest_start, '休憩開始時刻が記録されていません');
        $this->assertNotNull($attendance->rests->first()->rest_end, '休憩終了時刻が記録されていません');

        $response = $this->get(route('attendance.list'));
        $response->assertSee($attendance->rests->first()->rest_start->format('H:i'));
        $response->assertSee($attendance->rests->first()->rest_end->format('H:i'));
    }
}

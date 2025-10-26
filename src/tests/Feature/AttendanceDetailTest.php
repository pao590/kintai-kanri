<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_ログインユーザーの名前が表示される()
    {
        $user = User::factory()->create(['name' => 'テストユーザー']);
        $this->actingAs($user);

        $attendance = Attendance::factory()->for($user)->afterWork()->create();

        $response = $this->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
    }

    public function test_選択した日付が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $clockIn = Carbon::now()->subHours(9); // afterWork と同じ
        $attendance = Attendance::factory()->for($user)->afterWork()->create([
            'clock_in' => $clockIn,
        ]);

        $response = $this->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        // ビューに合わせて Y年n月j日 形式で確認
        $response->assertSee($clockIn->format('Y年n月j日'));
    }

    public function test_出勤退勤時間が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $clockIn = Carbon::now()->subHours(9)->setHour(9)->setMinute(0);
        $clockOut = Carbon::now()->subHours(9)->setHour(18)->setMinute(0);
        $attendance = Attendance::factory()->for($user)->afterWork()->create([
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ]);

        $response = $this->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee($clockIn->format('H:i'));
        $response->assertSee($clockOut->format('H:i'));
    }

    public function test_休憩時間が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->for($user)->afterWork()->create();

        $rest1Start = Carbon::now()->setHour(12)->setMinute(0);
        $rest1End = Carbon::now()->setHour(12)->setMinute(45);

        $rest2Start = Carbon::now()->setHour(15)->setMinute(0);
        $rest2End = Carbon::now()->setHour(15)->setMinute(15);

        $attendance->rests()->createMany([
            ['rest_start' => $rest1Start, 'rest_end' => $rest1End],
            ['rest_start' => $rest2Start, 'rest_end' => $rest2End],
        ]);

        $response = $this->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee($rest1Start->format('H:i'));
        $response->assertSee($rest1End->format('H:i'));
        $response->assertSee($rest2Start->format('H:i'));
        $response->assertSee($rest2End->format('H:i'));
    }
}

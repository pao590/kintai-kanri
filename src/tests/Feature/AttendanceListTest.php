<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_自分の勤怠情報が全て表示されている()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendances = Attendance::factory()->count(3)->for($user)->afterWork()->create();

        $response = $this->get(route('attendance.list'));

        foreach ($attendances as $attendance) {
            $response->assertSee(Carbon::parse($attendance->clock_in)->format('m/d'));
        }
    }

    public function test_勤怠一覧画面に現在の月が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('attendance.list'));

        $response->assertSee(now()->format('Y/m'));
    }

    public function test_前月ボタンで前月の勤怠情報が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $previousMonth = now()->subMonth();
        $attendance = Attendance::factory()->for($user)->afterWork()->create([
            'clock_in' => $previousMonth->startOfMonth()->addDay(),
            'clock_out' => $previousMonth->startOfMonth()->addDay()->addHours(8),
        ]);

        $response = $this->get(route('attendance.list', ['month' => $previousMonth->format('Y-m')]));

        $response->assertSee($attendance->clock_in->format('m/d'));
    }

    public function test_翌月ボタンで翌月の勤怠情報が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $nextMonth = now()->addMonth();
        $attendance = Attendance::factory()->for($user)->afterWork()->create([
            'clock_in' => $nextMonth->startOfMonth()->addDay(),
            'clock_out' => $nextMonth->startOfMonth()->addDay()->addHours(8),
        ]);

        $response = $this->get(route('attendance.list', ['month' => $nextMonth->format('Y-m')]));

        $response->assertSee($attendance->clock_in->format('m/d'));
    }

    public function test_詳細ボタンを押すとその日の勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->for($user)->afterWork()->create();

        $response = $this->get(route('attendance.show', $attendance->id));
        $response->assertStatus(200);
        $response->assertSee(Carbon::parse($attendance->clock_in)->format('H:i'));
    }
}

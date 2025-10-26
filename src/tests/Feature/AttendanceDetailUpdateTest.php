<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;

class AttendanceDetailUpdateTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_出勤時間が退勤時間より後の場合エラーになる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->for($user)->afterWork()->create();

        $response = $this->post(route('attendance.update', $attendance->id), [
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'reason' => '勤務修正',
        ]);

        $response->assertSessionHasErrors(['clock_in']);
        $this->assertStringContainsString('出勤時間が不適切な値です', session('errors')->first('clock_in'));
    }

    public function test_休憩開始時間が退勤時間より後の場合エラーになる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->for($user)->afterWork()->create();

        $response = $this->post(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => [
                ['start' => '19:00', 'end' => '19:30']
            ],
            'reason' => '勤務修正',
        ]);

        $response->assertSessionHasErrors(['rests.0.start']);
        $this->assertStringContainsString('休憩時間が不適切な値です', session('errors')->first('rests.0.start'));
    }

    public function test_休憩終了時間が退勤時間より後の場合エラーになる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->for($user)->afterWork()->create();

        $response = $this->post(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => [
                ['start' => '17:00', 'end' => '19:00']
            ],
            'reason' => '勤務修正',
        ]);

        $response->assertSessionHasErrors(['rests.0.end']);
        $this->assertStringContainsString('休憩時間もしくは退勤時間が不適切な値です', session('errors')->first('rests.0.end'));
    }

    public function test_備考欄未入力の場合エラーになる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->for($user)->afterWork()->create();

        $response = $this->post(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => '',
        ]);

        $response->assertSessionHasErrors(['reason']);
        $this->assertStringContainsString('備考を記入してください', session('errors')->first('reason'));
    }

    public function test_修正申請が作成され承認待ちとして表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->for($user)->afterWork()->create();

        $response = $this->post(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => '勤務修正',
        ]);

        $response->assertRedirect(route('attendance.show', $attendance->id));
        $this->assertDatabaseHas('attendance_correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'reason' => '勤務修正',
        ]);
    }
}

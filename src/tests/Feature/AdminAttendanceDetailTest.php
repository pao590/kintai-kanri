<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    protected function createAdmin()
    {
        return User::factory()->create(['role' => 1]);
    }

    public function test_勤怠詳細画面に選択したデータが表示される()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->addHours(9),
            'clock_out' => Carbon::today()->addHours(18),
            'status' => '出勤中',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.attendance.show', $attendance->id));
        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));
    }

    public function test_出勤時間が退勤時間より後の場合にエラーが出る()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin, 'admin');

        $response = $this->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'rests' => [],
            'reason' => 'テスト'
        ]);

        $response->assertSessionHasErrors(['clock_out' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    public function test_休憩開始時間が退勤時間より後の場合にエラーが出る()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => [
                ['start' => '19:00', 'end' => '19:30']
            ],
            'reason' => 'テスト'
        ]);

        $response->assertSessionHasErrors(['rests.0.start' => '休憩時間が不適切な値です']);
    }

    public function test_休憩終了時間が退勤時間より後の場合にエラーが出る()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => [
                ['start' => '12:00', 'end' => '19:00']
            ],
            'reason' => 'テスト'
        ]);

        $response->assertSessionHasErrors(['rests.0.end' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    public function test_備考欄が未入力の場合にエラーが出る()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin, 'admin');

        $response = $this->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => []
        ]);

        $response->assertSessionHasErrors(['reason' => '備考を記入してください']);
    }

    public function test_複数休憩を含めた正常更新()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin, 'admin');

        $response = $this->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => [
                ['start' => '12:00', 'end' => '12:30'],
                ['start' => '15:00', 'end' => '15:15'],
            ],
            'reason' => 'テスト'
        ]);

        $response->assertRedirect(route('admin.attendance.show', $attendance->id));

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => Carbon::today()->format('Y-m-d') . ' 09:00:00',
            'clock_out' => Carbon::today()->format('Y-m-d') . ' 18:00:00',
            'status' => '出勤中',
        ]);

        foreach ($attendance->rests as $index => $rest) {
            $this->assertDatabaseHas('rests', [
                'attendance_id' => $attendance->id,
                'rest_start' => Carbon::today()->format('Y-m-d') . ' ' . $this->formatTime($index, 'start'),
                'rest_end' => Carbon::today()->format('Y-m-d') . ' ' . $this->formatTime($index, 'end'),
            ]);
        }
    }

    protected function formatTime($index, $type)
    {
        $times = [
            0 => ['start' => '12:00:00', 'end' => '12:30:00'],
            1 => ['start' => '15:00:00', 'end' => '15:15:00'],
        ];
        return $times[$index][$type];
    }
}
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_その日になされた全ユーザーの勤怠情報が確認できる()
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $this->actingAs($admin, 'admin');

        $user1 = User::factory()->create(['name' => '山田太郎']);
        $user2 = User::factory()->create(['name' => '鈴木花子']);

        $today = Carbon::today();

        Attendance::factory()->create([
            'user_id' => $user1->id,
            'clock_in' => $today->copy()->addHour(),
            'clock_out' => $today->copy()->addHours(8),
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'clock_in' => $today->copy()->addHours(2),
            'clock_out' => $today->copy()->addHours(9),
        ]);

        $response = $this->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('鈴木花子');
        $response->assertSee($today->format('Y年m月d日'));
    }

    public function test_前日ボタンで前日の勤怠情報が表示される()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'admin');

        $user = User::factory()->create();

        $yesterday = Carbon::yesterday();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => $yesterday->copy()->addHour(),
            'clock_out' => $yesterday->copy()->addHours(8),
        ]);

        $response = $this->get(route('admin.attendance.list', ['date' => $yesterday->toDateString()]));

        $response->assertStatus(200);
        $response->assertSee($yesterday->format('Y年m月d日'));
        $response->assertSee($user->name);
    }

    public function test_翌日ボタンで翌日の勤怠情報が表示される()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'admin');

        $user = User::factory()->create();

        $tomorrow = Carbon::tomorrow();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => $tomorrow->copy()->addHour(),
            'clock_out' => $tomorrow->copy()->addHours(8),
        ]);

        $response = $this->get(route('admin.attendance.list', ['date' => $tomorrow->toDateString()]));

        $response->assertStatus(200);
        $response->assertSee($tomorrow->format('Y年m月d日'));
        $response->assertSee($user->name);
    }
}

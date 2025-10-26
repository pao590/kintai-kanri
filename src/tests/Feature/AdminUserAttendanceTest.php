<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AdminUserAttendanceTest extends TestCase
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

    public function test_管理者はユーザー一覧で名前とメールを確認できる()
    {
        $admin = $this->createAdmin();
        $users = User::factory()->count(2)->create(['role' => 0]);

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.staff.list'));
        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
            $response->assertSee(route('admin.attendance.staff', $user->id));
        }
    }

    public function test_管理者はユーザー月別勤怠一覧で出勤・退勤を確認できる()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['role' => 0]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::now()->startOfMonth()->addHours(9),
            'clock_out' => Carbon::now()->startOfMonth()->addHours(18),
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => Carbon::now()->format('Y-m')
        ]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));
        $response->assertSee(route('admin.attendance.show', $attendance->id));

        $response->assertSee(route('admin.attendance.staff', ['id' => $user->id, 'month' => Carbon::now()->subMonth()->format('Y-m')]));
        $response->assertSee(route('admin.attendance.staff', ['id' => $user->id, 'month' => Carbon::now()->addMonth()->format('Y-m')]));
    }

    public function test_管理者は勤怠日別一覧で名前・出勤・退勤・詳細を確認できる()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['role' => 0]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->addHours(9),
            'clock_out' => Carbon::today()->addHours(18),
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.attendance.list', ['date' => Carbon::today()->toDateString()]));
        $response->assertStatus(200);

        $response->assertSee($user->name);
        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));
        $response->assertSee(route('admin.attendance.show', $attendance->id));

        $response->assertSee(route('admin.attendance.list', ['date' => Carbon::today()->subDay()->format('Y-m-d')]));
        $response->assertSee(route('admin.attendance.list', ['date' => Carbon::today()->addDay()->format('Y-m-d')]));
    }

    public function test_管理者は勤怠詳細画面で情報とフォームを確認できる()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['role' => 0]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::now()->startOfDay()->addHours(9),
            'clock_out' => Carbon::now()->startOfDay()->addHours(18),
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.attendance.show', $attendance->id));
        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));
        $response->assertSee('<form', false);
    }
}

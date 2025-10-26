<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAttendanceCorrectionTest extends TestCase
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

    public function test承認待ちの修正申請が一覧で確認できる()
    {
        $admin = $this->createAdmin();
        $requests = AttendanceCorrectionRequest::factory()->count(2)->create(['status' => 'pending']);
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.requests.index', ['status' => 'pending']));
        $response->assertStatus(200);

        foreach ($requests as $request) {
            $response->assertSeeText($request->user->name);
            $response->assertSeeText($request->reason);
        }
    }

    public function test承認済みの修正申請が一覧で確認できる()
    {
        $admin = $this->createAdmin();
        $requests = AttendanceCorrectionRequest::factory()->count(2)->approved()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.requests.index', ['status' => 'approved']));
        $response->assertStatus(200);

        foreach ($requests as $request) {
            $response->assertSeeText($request->user->name);
            $response->assertSeeText($request->reason);
        }
    }

    public function test_修正申請の詳細画面で内容が確認できる()
    {
        $admin = $this->createAdmin(); // ← 修正
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        $request = AttendanceCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'reason' => '理由',
            'status' => 'pending',
            'correction_content' => 'テスト修正内容', // ← 追加済みOK！
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.requests.show', $request->id));
        $response->assertStatus(200);
        $response->assertSeeText($request->user->name);
        $response->assertSeeText($request->reason);
        $response->assertSee('テスト修正内容');
    }



    public function test修正申請を承認するとステータスがapprovedになる()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $request = AttendanceCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending'
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->post(route('admin.requests.approve', $request->id));
        $response->assertRedirect(route('admin.requests.approve.view', $request->id));

        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);
    }
}

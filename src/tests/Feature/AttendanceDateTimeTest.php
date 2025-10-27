<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;


class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_現在日時が画面上に正しい形式で表示される()
    {
        Carbon::setTestNow($now = Carbon::now());

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => $now,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);

        $expected = $attendance->clock_in->format('Y年n月j日');
        $response->assertSee($expected);
    }
}

<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'clock_in' => Carbon::now(),
            'clock_out' => null,
            'status' => '出勤中',
        ];
    }

    public function beforeWork()
    {
        return $this->state(fn() => [
            'clock_in' => null,
            'clock_out' => null,
            'status' => '勤務外',
        ]);
    }

    public function working()
    {
        return $this->state(fn() => [
            'clock_in' => Carbon::now()->subHours(3),
            'clock_out' => null,
            'status' => '出勤中',
        ]);
    }

    public function onBreak()
    {
        return $this->state(fn() => [
            'clock_in' => Carbon::now()->subHours(3),
            'clock_out' => null,
            'status' => '休憩中',
        ]);
    }

    public function afterWork()
    {
        return $this->state(fn() => [
            'clock_in' => Carbon::now()->subHours(9),
            'clock_out' => Carbon::now(),
            'status' => '退勤済',
        ]);
    }
}

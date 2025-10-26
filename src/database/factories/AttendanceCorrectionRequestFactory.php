<?php

namespace Database\Factories;

use App\Models\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceCorrectionRequestFactory extends Factory
{
    protected $model = AttendanceCorrectionRequest::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'user_id' => User::factory(),
            'correction_content' => $this->faker->paragraph(2),
            'reason' => $this->faker->sentence(),
            'status' => 'pending',                              
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function approved()
    {
        return $this->state(fn() => [
            'status' => 'approved',
        ]);
    }

    public function withCorrectionContent(string $content)
    {
        return $this->state(fn() => [
            'correction_content' => $content,
        ]);
    }
    
    public function withReason(string $reason)
    {
        return $this->state(fn() => [
            'reason' => $reason,
        ]);
    }
}

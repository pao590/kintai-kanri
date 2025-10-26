<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Rest;
use Carbon\Carbon;

class RestFactory extends Factory
{
    protected $model = Rest::class;

    public function definition()
    {
        $date = Carbon::today();
        return [
            'attendance_id' => null, // テストで明示的に指定
            'rest_start' => $date->copy()->addHours(12),
            'rest_end' => $date->copy()->addHours(12)->addMinutes(30),
        ];
    }
}

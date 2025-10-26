<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() || auth()->guard('admin')->check();
    }

    public function rules()
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $clock_in = Carbon::parse($this->clock_in);
                    $clock_out = Carbon::parse($value);
                    if ($clock_out->lte($clock_in)) {
                        $fail('出勤時間もしくは退勤時間が不適切な値です');
                    }
                }
            ],
            'rests' => ['array'],
            'rests.*.start' => [
                'nullable',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if (!$value) return;

                    $clock_in = Carbon::parse($this->clock_in);
                    $clock_out = Carbon::parse($this->clock_out);
                    $rest_start = Carbon::parse($value);

                    if ($rest_start->lt($clock_in) || $rest_start->gt($clock_out)) {
                        $fail('休憩時間が不適切な値です');
                    }

                    $rest_end_value = $this->input(str_replace('start', 'end', $attribute));
                    if ($rest_end_value && $rest_start->gt(Carbon::parse($rest_end_value))) {
                        $fail('休憩時間が不適切な値です');
                    }
                }
            ],
            'rests.*.end' => [
                'nullable',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if (!$value) return;

                    $clock_out = Carbon::parse($this->clock_out);
                    $rest_start_value = $this->input(str_replace('end', 'start', $attribute));
                    $rest_start = $rest_start_value ? Carbon::parse($rest_start_value) : null;
                    $rest_end = Carbon::parse($value);

                    if ($rest_start && $rest_end->lt($rest_start)) {
                        $fail('休憩時間もしくは退勤時間が不適切な値です');
                    }

                    if ($rest_end->gt($clock_out)) {
                        $fail('休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            ],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください。',
            'clock_out.required' => '退勤時間を入力してください。',
            'reason.required' => '備考を記入してください',
        ];
    }
}

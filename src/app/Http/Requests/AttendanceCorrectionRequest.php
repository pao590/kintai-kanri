<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in'  => ['required', 'date_format:H:i', 'before_or_equal:clock_out'],
            'clock_out' => ['required', 'date_format:H:i', 'after_or_equal:clock_in'],
            'rests.*.start' => ['nullable', 'date_format:H:i', 'after_or_equal:clock_in', 'before_or_equal:clock_out'],
            'rests.*.end'   => ['nullable', 'date_format:H:i', 'before_or_equal:clock_out'],
            'reason'    => ['required', 'string', 'max:500'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください。',
            'clock_in.before_or_equal' => '出勤時間が不適切な値です',
            'clock_out.required' => '退勤時間を入力してください。',
            'clock_out.after_or_equal' => '退勤時間が不適切な値です',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切です',
            'rests.*.start.after_or_equal' => '休憩時間が不適切な値です',
            'rests.*.start.before_or_equal' => '休憩時間が不適切な値です',
            'rests.*.end.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'rests.*.end.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $rests = $this->input('rests', []);
            foreach ($rests as $i => $rest) {
                if (!empty($rest['start']) && !empty($rest['end'])) {
                    if ($rest['end'] <= $rest['start']) {
                        $validator->errors()->add("rests.$i.end", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }
}

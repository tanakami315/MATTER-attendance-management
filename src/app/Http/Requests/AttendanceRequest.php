<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
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
            'clock_in' => [
                'required',
                'date_format:H:i',
            ],
            'clock_out' => [
                'required',
                'date_format:H:i',
                'after:clock_in',
            ],
            'start_break' => [
                'array',
            ],
            'end_break' => [
                'array',
            ],
            'start_break.*' => [
                'nullable',
                'date_format:H:i',
                'after:clock_in',
                'before:clock_out',
            ],
            'end_break.*' => [
                'nullable',
                'date_format:H:i',
                'before:clock_out',
            ],
            'comment' =>[
                'required'
            ]
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'comment.required' => '備考を記入してください',

            'clock_in.date_format' => '勤務開始時間は半角数字 HH:MM 形式で入力してください',
            'clock_out.date_format' => '勤務終了時間は半角数字 HH:MM 形式で入力してください',
            'start_break.*.date_format' => '休憩開始時間は半角数字 HH:MM 形式で入力してください',
            'end_break.*.date_format' => '休憩終了時間は半角数字 HH:MM 形式で入力してください',

            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'start_break.*.after' => '休憩時間が不適切な値です',
            'start_break.*.before' => '休憩時間が不適切な値です',
            'end_break.*.before' => '休憩時間もしくは退勤時間が不適切な値です',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startBreaks = $this->input('start_break', []);
            $endBreaks = $this->input('end_break', []);

            foreach ($endBreaks as $index => $endBreak) {
                $startBreak = $startBreaks[$index] ?? null;

                if ($startBreak && $endBreak && $endBreak <= $startBreak) {
                    $validator->errors()->add(
                        "end_break.$index",
                        '休憩時間が不適切な値です'
                    );
                }
            }
        });
    }
}
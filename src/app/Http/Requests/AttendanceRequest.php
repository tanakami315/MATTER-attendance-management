<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'clock_in' => [
                'required',
                'date_format:H:i',
            ],
            'clock_out' => [
                'required',
                'date_format:H:i',
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
            ],
            'end_break.*' => [
                'nullable',
                'date_format:H:i',
            ],
            'comment' =>[
                'required',
                'max:255',
            ],
        ];
    }

    /**
     * Get the error messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'comment.required' => '備考を記入してください',

            'clock_in.date_format' => '勤務開始時間は半角数字 HH:MM 形式で入力してください',
            'clock_out.date_format' => '勤務終了時間は半角数字 HH:MM 形式で入力してください',
            'start_break.*.date_format' => '休憩開始時間は半角数字 HH:MM 形式で入力してください',
            'end_break.*.date_format' => '休憩終了時間は半角数字 HH:MM 形式で入力してください',

            'comment.max' => '備考は255文字以内で入力してください',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {

            // 出退勤時間のフォーマットエラーがあればスキップ
            if ($validator->errors()->has('clock_in') ||
                $validator->errors()->has('clock_out')) {
                return;
            }

            if ($this->clock_out <= $this->clock_in) {
                $validator->errors()->add(
                    'clock_out',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            // 休憩時間のチェック
            $startBreaks = $this->input('start_break', []);
            $endBreaks = $this->input('end_break', []);

            $breakIndexes = array_unique(array_merge(
                array_keys($startBreaks),
                array_keys($endBreaks)
            ));

            foreach ($breakIndexes as $index) {
                $startBreak = $startBreaks[$index] ?? null;
                $endBreak = $endBreaks[$index] ?? null;

                 // 休憩時間のフォーマットエラーがあればスキップ
                if (
                    $validator->errors()->has("start_break.$index") ||
                    $validator->errors()->has("end_break.$index")
                ) {
                    continue;
                }

                if ($startBreak && $startBreak <= $this->clock_in) {
                    $validator->errors()->add(
                        "start_break.$index",
                        '休憩時間が不適切な値です'
                    );
                }

                if ($startBreak && $startBreak >= $this->clock_out) {
                    $validator->errors()->add(
                        "start_break.$index",
                        '休憩時間が不適切な値です'
                    );
                }

                if ($startBreak && $endBreak && $endBreak <= $startBreak) {
                    $validator->errors()->add(
                        "end_break.$index",
                        '休憩時間が不適切な値です'
                    );
                }

                if ($endBreak && $endBreak >= $this->clock_out) {
                    $validator->errors()->add(
                        "end_break.$index",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        });
    }
}
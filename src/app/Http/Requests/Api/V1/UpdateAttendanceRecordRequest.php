<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Attendance;

class UpdateAttendanceRecordRequest extends FormRequest
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
        $attendance = Attendance::find($this->route('attendanceRecord'));

        return [
            'date' => [
                'required',
                'date_format:Y-m-d',
                    Rule::unique('attendances', 'date')
                        ->ignore($this->route('attendanceRecord'))
                        ->where(function ($query) use ($attendance) {
                            if (! $attendance) {
                                return $query->whereRaw('1 = 0');
                            }
                            return $query->where('user_id', $attendance->user_id);
                        }),
            ],
            'clock_in' => [
                'required',
                'date_format:H:i:s',
            ],
            'clock_out' => [
                'nullable',
                'date_format:H:i:s',
                'after:clock_in',
            ],
            'comment' => [
                'nullable',
                'max:255',
            ],
        ];
    }

    public function messages()
    {
        return [
            'date.required' => '必須未入力：勤怠日は必須です。',
            'date.date_format' => '形式不正：勤怠日は YYYY-MM-DD 形式で指定してください。',
            'date.unique' => 'user_id × date重複:この日付の勤怠は既に登録されています。',
            'clock_in.required' => '必須未入力：出勤時刻は必須です。',
            'clock_in.date_format' => '形式不正：出勤時刻は HH:MM:SS 形式で指定してください。',
            'clock_out.date_format' => '形式不正：退勤時刻は HH:MM:SS 形式で指定してください。',
            'clock_out.after' => 'clock_inより前：退勤時刻は出勤時刻より後の時刻を指定してください。',
            'comment.max' => '255文字超：備考は255文字以内で入力してください'
        ];
    }
}

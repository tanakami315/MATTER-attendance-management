<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakCorrectRequest;
use Carbon\Carbon;

class AttendanceCorrectRequestTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 勤怠ID80
        $attendance = Attendance::findOrFail(80);

        $correctRequest = AttendanceCorrectRequest::create([
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::parse($attendance->date->format('Y-m-d') . ' 08:00'),
            'clock_out' => Carbon::parse($attendance->date->format('Y-m-d') . ' 18:00'),
            'comment' => '打刻忘れのため',
            'status' => 0,
        ]);

        BreakCorrectRequest::create([
            'attendance_correct_request_id' => $correctRequest->id,
            'start_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' 12:30'),
            'end_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' 13:30'),
        ]);

        // 勤怠ID150
        $attendance = Attendance::findOrFail(150);

        $correctRequest = AttendanceCorrectRequest::create([
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::parse($attendance->date->format('Y-m-d') . ' 09:00'),
            'clock_out' => Carbon::parse($attendance->date->format('Y-m-d') . ' 22:00'),
            'comment'=>
                "お疲れ様です。\n" .
                "退勤するつもりで打刻を行ったのですが、その後客先から電話があり緊急対応を迫られ残業を行いました。\n" .
                "残業が長時間になってしまったため、残業中に休憩を挟んでおります。\n" .
                "打刻忘れはよくないことだとは承知していますが、緊急対応のためやむをえませんでした。\n" .
                "また、残業時間が長くなりましたのでその点も把握いただきたいと考えております。\n" .
                "承認よろしくお願いいたします。",
            'status' => 1,
        ]);

        BreakCorrectRequest::create([
            'attendance_correct_request_id' => $correctRequest->id,
            'start_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' 12:30'),
            'end_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' 13:30'),
        ]);

        BreakCorrectRequest::create([
            'attendance_correct_request_id' => $correctRequest->id,
            'start_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' 19:00'),
            'end_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' 19:30'),
        ]);

        $attendance->update([
            'clock_in' => $correctRequest->clock_in,
            'clock_out' => $correctRequest->clock_out,
            'comment' => $correctRequest->comment,
        ]);

        // 元の休憩を削除
        $attendance->breakTimes()->delete();

        // 修正申請と同じ休憩を作成
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' 12:30'),
            'end_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' 13:30'),
        ]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' 19:00'),
            'end_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' 19:30'),
        ]);

        // 勤怠ID177
        $attendance = Attendance::findOrFail(177);

        $correctRequest = AttendanceCorrectRequest::create([
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::parse($attendance->date->format('Y-m-d') . ' 09:00'),
            'clock_out' => Carbon::parse($attendance->date->format('Y-m-d') . ' 18:00'),
            'comment' => '電車遅延のため',
            'status' => 0,
        ]);

        BreakCorrectRequest::create([
            'attendance_correct_request_id' => $correctRequest->id,
            'start_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' 12:30'),
            'end_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' 13:30'),
        ]);
    }
}

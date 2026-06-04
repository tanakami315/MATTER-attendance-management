<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = Attendance::with('breaks')
            ->where('user_id', auth()->id())
            ->whereDate('date', today())
            ->first();

        // 勤務開始前
        $status = 'before_work';

        if ($attendance) {

            // 退勤済み
            if ($attendance->clock_out) {
                $status = 'after_work';
            }

            // 退勤前
            else {

                // 終了していない休憩
                $break = $attendance->breaks
                    ->whereNull('end_break')
                    ->count();

                if ($break) {
                    $status = 'break';
                } else {
                    $status = 'working';
                }
            }
        }

        return view('staff.index', compact('attendance', 'status'));
    }

    // 勤務開始
    public function start_work(Request $request)
    {
        $attendance['user_id'] = auth()->id();
        $attendance['date'] = date('Y-m-d');
        $attendance['clock_in'] = now();
        Attendance::create($attendance);

        return redirect('/attendance');
    }

    // 勤務終了
    public function end_work(Request $request)
    {
        $attendance['clock_out'] = now();
        Attendance::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->update($attendance);
        return redirect('/attendance');
    }

    // 休憩開始
    public function start_break(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->first();

        $break['attendance_id'] = $attendance->id;
        $break['start_break'] = now();
        BreakTime::create($break);

        return redirect('/attendance');
    }

    // 休憩終了
    public function end_break(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->first();

        $break['attendance_id'] = $attendance->id;
        $break['end_break'] = now();
        BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('end_break')
            ->update($break);

        return redirect('/attendance');
    }

    public function list(request $request)
    {
        $user = auth()->user();
        return view('staff.list');
    }
}

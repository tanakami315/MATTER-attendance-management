<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakCorrectRequest;

class StaffController extends Controller
{
    public function stamp()
    {
        $attendance = Attendance::with('breakTimes')
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
                $break = $attendance->breakTimes
                    ->whereNull('end_break')
                    ->count();

                if ($break) {
                    $status = 'break';
                } else {
                    $status = 'working';
                }
            }
        }

        return view('staff.staff_stamp', compact('attendance', 'status'));
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

    // 勤怠一覧
    public function monthlyList(Request $request)
    {
        $month = Carbon::parse(
            $request->month ?? now()->format('Y-m')
        );

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', auth()->id())
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(function ($attendance) {
                return $attendance->date->format('Y-m-d');
            });

        $dates = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dates[] = $date->copy();
        }

        $prevMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');
        
        return view('staff.staff_monthly_list', compact(
            'month',
            'dates',
            'attendances',
            'prevMonth',
            'nextMonth'
        ));
    }

    // 勤怠詳細表示
    public function detail($id)
    {
        $attendance = Attendance::with('user','breakTimes')
            ->findOrFail($id);

        $breakTimes = BreakTime::where(
            'attendance_id',
            $attendance->id
            )
            ->get();

        $attendanceCorrectRequest = AttendanceCorrectRequest::where(
            'attendance_id',
            $attendance->id
            )
            ->latest()
            ->first();
        
        $breakCorrectRequests = null;

        if ($attendanceCorrectRequest) {
            $breakCorrectRequests = BreakCorrectRequest::where(
                'attendance_correct_request_id',
                $attendanceCorrectRequest->id
                )
                ->latest()
                ->get();

        }

        return view(
            'staff.staff_detail',
            compact(
                'attendance',
                'breakTimes',
                'attendanceCorrectRequest',
                'breakCorrectRequests'
            )
        );
    }

    // 申請作成
    public function store(Request $request, $attendance_id)
    {
        $attendance = Attendance::findOrFail($attendance_id);

        $attendanceCorrectRequest = AttendanceCorrectRequest::create([
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $request->clock_in),
            'clock_out' => Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $request->clock_out),
            'comment' => $request->comment,
            'status' => 0,
        ]);

        $startBreaks = $request->input('start_break', []);
        $endBreaks = $request->input('end_break', []);

        foreach ($startBreaks as $index => $startBreak) {
            $endBreak = $endBreaks[$index] ?? null;

            if ($startBreak && $endBreak) {
                BreakCorrectRequest::create([
                    'attendance_correct_request_id' => $attendanceCorrectRequest->id,
                    'start_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $startBreak),
                    'end_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $endBreak),
                ]);
            }
        }


        return redirect('/stamp_correction_request/list')
            ->with('flashSuccess', '申請を作成しました');
    }

   // 申請一覧(管理者共通)
    public function correctRequestList(Request $request)
    {
        $tab = $request->query('tab');

        $user = auth()->user();

        // 管理者
        if ($user->admin_status == 1){
            $query = AttendanceCorrectRequest::with('attendance.user');
        } 
        // スタッフ
        else {$query = AttendanceCorrectRequest::with('attendance.user')
        ->whereHas('attendance', function ($query) {
            $query->where('user_id', auth()->id());
        });
        }

        if ($tab === 'pending') {
            $query->where('status', 0);
        } elseif ($tab === 'approved') {
            $query->where('status', 1);
        }
        $attendanceCorrectRequests = $query->latest()->get();

        // 管理者
        if ($user->admin_status == 1){
            return view(
                'admin.admin_correct_request_list',
                compact('attendanceCorrectRequests')
            );
        }

        // スタッフ
        else {
            return view(
                'staff.staff_correct_request_list',
                compact('attendanceCorrectRequests')
            );
        }
    }
}
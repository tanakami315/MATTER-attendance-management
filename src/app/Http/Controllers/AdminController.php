<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakCorrectRequest;

class AdminController extends Controller
{
    // 日別勤怠一覧
    public function adminDailyList(Request $request)
    {
        $day = Carbon::parse(
            $request->day ?? now()->format('Y-m-d')
        );

        $attendances = Attendance::with('user','breakTimes')
            ->whereDate('date', $day)
            ->get();

        $prevDay = $day->copy()->subDay()->format('Y-m-d');
        $nextDay = $day->copy()->addDay()->format('Y-m-d');

        return view('admin.admin_daily_list', compact(
            'day',
            'attendances',
            'prevDay',
            'nextDay'
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
        
        $breakCorrectRequest = null;

        if ($attendanceCorrectRequest) {
            $breakCorrectRequests = BreakCorrectRequest::where(
                'attendance_correct_request_id',
                $attendanceCorrectRequest->id
                )
                ->latest()
                ->get();

            $breakCorrectRequest = $breakCorrectRequests->get(0);
        }

        return view(
            'admin.admin_detail',
            compact(
                'attendance',
                'breakTimes',
                'attendanceCorrectRequest',
                'breakCorrectRequest'
            )
        );
    }

    // スタッフ一覧
    public function adminStaffList()
    {
        $users = User::all();
        return view('admin.admin_staff_list', compact('users'));
    }

    // 申請承認
    public function approve($attendance_correct_request_id)
    {
        $attendanceCorrectRequest = AttendanceCorrectRequest::with([
            'attendance.breakTimes',
            'breakCorrectRequests',
        ])->findOrFail($attendance_correct_request_id);
        
        $attendance = $attendanceCorrectRequest->attendance;

        $attendance->update([
            'clock_in' => $attendanceCorrectRequest->clock_in,
            'clock_out' => $attendanceCorrectRequest->clock_out,
            'comment' => $attendanceCorrectRequest->comment,
        ]);

        $attendance->breakTimes()->delete();

        foreach ($attendanceCorrectRequest->breakCorrectRequests as $breakCorrectRequest) {
            if ($breakCorrectRequest->start_break && $breakCorrectRequest->end_break) {
                $attendance->breakTimes()->create([
                    'start_break' => $breakCorrectRequest->start_break,
                    'end_break' => $breakCorrectRequest->end_break,
                ]);
            }
        }
                
        $attendanceCorrectRequest->update([
            'status' => 1,
        ]);

        return redirect('/stamp_correction_request/list')
            ->with('flashSuccess', '申請を承認しました');
    }    

    // 勤怠修正
    public function updateAttendance(Request $request, $attendance_id)
    {
        $attendance = Attendance::with(['breakTimes'])
            ->findOrFail($attendance_id);
        
        $date = $attendance->date->format('Y-m-d');

        $attendance->update([
            'clock_in' => Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $request->clock_in),
            'clock_out' => Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $request->clock_out),
            'comment' => $request->comment,
        ]);

        $attendance->breakTimes()->delete();

        foreach ($request->start_break ?? [] as $index => $startBreak) {
            $endBreak = $request->end_break[$index] ?? null;

            if ($startBreak && $endBreak) {
                $attendance->breakTimes()->create([
                    'start_break' => Carbon::parse($date . ' ' . $startBreak),
                    'end_break' => Carbon::parse($date . ' ' . $endBreak),
                ]);
            }
        }

        return redirect('/admin/attendance/' . $attendance_id)
            ->with('flashSuccess', '勤怠を更新しました');
    }

    // スタッフ別月次勤怠一覧
    public function adminMonthlyList($user_id, Request $request)
    {
        $month = Carbon::parse(
            $request->month ?? now()->format('Y-m')
        );
        
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $user = User::findOrFail($user_id);

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user_id)
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
        
        return view('admin.admin_monthly_list', compact(
            'month',
            'dates',
            'user',
            'attendances',
            'prevMonth',
            'nextMonth'
        ));
    }

    // 申請一覧
    public function adminCorrectRequestList(Request $request)
    {
        $tab = $request->query('tab');

        $query = AttendanceCorrectRequest::with('attendance.user');

        if ($tab === 'pending') {
            $query->where('status', 0);
        } elseif ($tab === 'approved') {
            $query->where('status', 1);
        }

        $attendanceCorrectRequests = $query->latest()->get();    

        return view(
            'admin.admin_correct_request_list',
            compact('attendanceCorrectRequests')
        );
    }
}

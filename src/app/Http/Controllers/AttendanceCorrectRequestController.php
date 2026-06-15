<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakCorrectRequest;

class AttendanceCorrectRequestController extends Controller
{
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
            'staff.detail',
            compact(
                'attendance',
                'breakTimes',
                'attendanceCorrectRequest',
                'breakCorrectRequest'
            )
        );
    }

    // 勤務修正申請
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


        return redirect('/attendance');
    }

    // 申請一覧
    public function correctRequestList(Request $request)
    {
        //スタッフ画面（自分の申請のみ） 
        if (session('login_type')==='staff'){
            $attendanceCorrectRequests = AttendanceCorrectRequest::with('attendance.user')
            ->whereHas('attendance', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->get();
        } 
        // 管理者画面（全ての申請）
        else {
            $attendanceCorrectRequests = AttendanceCorrectRequest::with('attendance.user')
            ->latest()
            ->get();
        }

        return view(
            'staff.correct_request_list',
            compact('attendanceCorrectRequests')
        );
    }

    public function showApprove($id)
    {
        $attendance = Attendance::with('user','breakTimes')
            ->findOrFail($id);

        $break1 = $attendance->breakTimes->get(0);
        $break2 = $attendance->breakTimes->get(1);

        $pendingCorrectRequest = AttendanceCorrectRequest::where(
            'attendance_id',
            $attendance->id
            )
            ->where('status', 0)
            ->exists();

        return view(
            'admin.admin_approve',
            compact(
                'attendance',
                'break1',
                'break2', 
                'pendingCorrectRequest'
            )
        );
    }

    // 申請承認
    public function approve($attendance_correct_request_id)
    {
        $attendanceCorrectRequest = AttendanceCorrectRequest::findOrFail($attendance_correct_request_id);
        $attendanceCorrectRequest->status = 1;
        $attendanceCorrectRequest->save(); 

        return redirect('/admin/attendance/list'); 
    }    

}
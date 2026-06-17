<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
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
            'common.detail',
            compact(
                'attendance',
                'breakTimes',
                'attendanceCorrectRequest',
                'breakCorrectRequest'
            )
        );
    }

    // 申請作成（スタッフ）
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

    // 申請承認（管理者）
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

    // 勤怠修正（管理者）
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

    // 申請一覧
    public function correctRequestList(Request $request)
    {
        $tab = $request->query('tab');

        //スタッフ画面（自分の申請のみ） 
        if (session('login_type')==='staff'){
            $query = AttendanceCorrectRequest::with('attendance.user')
            ->whereHas('attendance', function ($query) {
                $query->where('user_id', auth()->id());
            });

            if ($tab === 'pending') {
                $query->where('status', 0);
            } elseif ($tab === 'approved') {
                $query->where('status', 1);
            }
            $attendanceCorrectRequests = $query->latest()->get();
        } 

        // 管理者画面（全ての申請）
        else {
            $query = AttendanceCorrectRequest::with('attendance.user');

            if ($tab === 'pending') {
                $query->where('status', 0);
            } elseif ($tab === 'approved') {
                $query->where('status', 1);
            }

            $attendanceCorrectRequests = $query->latest()->get();    
        }

        return view(
            'common.correct_request_list',
            compact('attendanceCorrectRequests')
        );
    }
}
    
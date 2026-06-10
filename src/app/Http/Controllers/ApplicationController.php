<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Application;
use App\Models\ApplicationBreak;

class ApplicationController extends Controller
{
    // 勤務修正申請
    public function store(Request $request, $attendance_id)
    {
        $attendance = Attendance::findOrFail($attendance_id);

        $application = Application::create([
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $request->clock_in),
            'clock_out' => Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $request->clock_out),
            'comment' => $request->comment,
            'status' => 0,
        ]);

        if ($request->start_break && $request->end_break) {
            ApplicationBreak::create([
                'application_id' => $application->id,
                'start_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $request->start_break),
                'end_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $request->end_break),
            ]);
        }

        if ($request->start_break2 && $request->end_break2) {
            ApplicationBreak::create([
                'application_id' => $application->id,
                'start_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $request->start_break2),
                'end_break' => Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $request->end_break2),
            ]);
        }

        return redirect('/attendance');
    }

    // 申請一覧
    public function applicationList(Request $request)
    {
        //スタッフ画面（自分の申請のみ） 
        if (session('login_type')==='staff'){
            $applications = Application::with('attendance.user')
            ->whereHas('attendance', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->get();
        } 
        // 管理者画面（全ての申請）
        else {
            $applications = Application::with('attendance.user')
            ->latest()
            ->get();
        }

        return view(
            'staff.application-list',
            compact('applications')
        );
    }

    public function showApprove($id)
    {
        $attendance = Attendance::with('user','breakTimes')
            ->findOrFail($id);

        $break1 = $attendance->breakTimes->get(0);
        $break2 = $attendance->breakTimes->get(1);

        $pendingApplication = Application::where(
            'attendance_id',
            $attendance->id
            )
            ->where('status', 0)
            ->exists();

        return view(
            'admin.admin-approve',
            compact(
                'attendance',
                'break1',
                'break2', 
                'pendingApplication'
            )
        );
    }

    // 申請承認
    public function approve($application_id)
    {
        $application = Application::findOrFail($application_id);
        $application->status = 1;
        $application->save();  
    }    

}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Application;

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

    // スタッフ一覧
    public function adminStaffList()
    {
        $users = User::all();
        return view('admin.admin_staff_list', compact('users'));
    }

    // スタッフ別月次勤怠一覧
    public function adminMonthlyList($user_id, Request $request)
    {
        $month = Carbon::parse(
            $request->month ?? now()->format('Y-m')
        );
        
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

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
            'attendances',
            'prevMonth',
            'nextMonth'
        ));
    }
}

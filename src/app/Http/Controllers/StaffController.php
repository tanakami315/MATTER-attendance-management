<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequest;
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
        $attendance = Attendance::with([
            'user',
            'breakTimes',
            'attendanceCorrectRequests.breakCorrectRequests',
        ])->findOrFail($id);

        $breakTimes = $attendance->breakTimes;


        $attendanceCorrectRequest = AttendanceCorrectRequest::where(
            'attendance_id',
            $attendance->id
            )
            ->latest()
            ->first();

         $attendanceCorrectRequest = $attendance-> attendanceCorrectRequests
            ->sortByDesc('created_at')
            ->first();
        
        $breakCorrectRequests = $attendanceCorrectRequest
            ? $attendanceCorrectRequest->breakCorrectRequests
            : collect();

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
    public function store(AttendanceRequest $request, $attendance_id)
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

    // レポート
    public function report()
    {
        // 月次
        $monthlyTotals = collect();

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');

            $monthlyTotals[$month] = [
                'work_minutes' => 0,
                'overtime_minutes' => 0,
                'work_days' => 0,
                'work_time' => '0h 00m',
                'overtime_time' => '0h 00m',
            ];
        }

        Attendance::with('breakTimes')
            ->where('user_id', auth()->id())
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->get()
            ->groupBy(function ($attendance) {
                return $attendance->date->format('Y-m');
            })
            ->each(function ($attendances, $month) use (&$monthlyTotals) {

                //出退勤が終了している日数のみ抽出
                $completedAttendances = $attendances->filter(function ($attendance) {
                    return $attendance->clock_in && $attendance->clock_out;
                });

                // 総労働時間（1カ月）
                $workMinutes = $completedAttendances->sum(function ($attendance) {
                    return $attendance->work_minutes;
                });

                // 就労日数（1カ月）
                $workDays = $completedAttendances->count();

                // 残業時間（分) 8時間超過分の和
                $overtimeMinutes = $completedAttendances->sum(function ($attendance) {
                    return max(
                        0,
                        $attendance->work_minutes - 8 * 60
                    );
                });

                $monthlyTotals[$month] = [
                    'work_minutes' => $workMinutes,
                    'overtime_minutes' => $overtimeMinutes,
                    'work_days' => $workDays,
                    'work_time' => sprintf(
                        '%dh %02dm',
                        floor($workMinutes / 60),
                        $workMinutes % 60
                    ),
                    'overtime_time' => sprintf(
                        '%dh %02dm',
                        floor($overtimeMinutes / 60),
                        $overtimeMinutes % 60
                    ),
                ];
            });

            // 基本サマリー
            // 総労働時間（6ヵ月）
            $totalWorkMinutes = $monthlyTotals->sum('work_minutes');
            // 総残業時間（6ヵ月）
            $totalOvertimeMinutes = $monthlyTotals->sum('overtime_minutes');
            // 総出勤日数（6ヵ月）
            $totalWorkDays = $monthlyTotals->sum('work_days');
            // 平均労働時間（6ヵ月から算出）/日
            $averageWorkMinutes = floor($totalWorkMinutes / $totalWorkDays);

            $summary = [
                'total_work_time' => sprintf('%dh %02dm', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60),
                'total_overtime_time' => sprintf('%dh %02dm', floor($totalOvertimeMinutes / 60), $totalOvertimeMinutes % 60),
                'average_work_time' => sprintf('%dh %02dm', floor($averageWorkMinutes / 60), $averageWorkMinutes % 60),
            ];

            // 今月の異常検知
            $thisMonthAttendances = Attendance::with('breakTimes')
                ->where('user_id', auth()->id())
                ->whereBetween('date', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ])
                ->get();

            $lateCount = 0;
            $earlyLeaveCount = 0;
            $longWorkCount = 0;

            //出退勤が終了している日数のみ抽出
            $completedAttendances = $thisMonthAttendances->filter(function ($attendance) {
                return $attendance->clock_in && $attendance->clock_out;
            });

            // 遅刻（9:00より後）
            $lateAttendances= $completedAttendances->filter(function ($attendance) {
                return $attendance->clock_in->format('H:i') > '09:00';
            });
            $lateCount = $lateAttendances->count();

            // 早退（18:00より前）
            $earlyLeaveAttendances= $completedAttendances->filter(function ($attendance) {
                return $attendance->clock_out->format('H:i') < '18:00';
            });
            $earlyLeaveCount = $earlyLeaveAttendances->count();
            
            // 長時間労働（10時間より長い）
            $longWorkAttendances= $completedAttendances->filter(function ($attendance) {
                return $attendance->work_minutes > 10 * 60 ;
            });
            $longWorkCount = $longWorkAttendances->count();
        
            return view('staff.staff_report', compact(
                'monthlyTotals',
                'summary',
                'lateCount',
                'earlyLeaveCount',
                'longWorkCount',
            ));
            }
}

<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakTime;
use App\Models\BreakCorrectRequest;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StaffController extends Controller
{
    /**
     * Show the attendance register view for staff.
     *
     * @return View
     */
    public function stamp(): View
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

    /**
     * Register the start of work for staff.
     *
     * @return RedirectResponse
     */
    public function start_work(): RedirectResponse
    {
        Attendance::create([
            'user_id' => auth()->id(),
            'date' => today(),
            'clock_in' => now(),
        ]);

        return redirect('/attendance');
    }

    /**
     * Register the end of work for staff.
     *
     * @return RedirectResponse
     */
    public function end_work(): RedirectResponse
    {
        Attendance::where('user_id', auth()->id())
            ->update([
                'clock_out' => now(),
            ]);
        return redirect('/attendance');
    }

    /**
     * Register the start of break for staff.
     *
     * @return RedirectResponse
     */
    public function start_break(): RedirectResponse
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->first();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_break' => now(),
        ]);

        return redirect('/attendance');
    }

    /**
     * Register the end of break for staff.
     *
     * @return RedirectResponse
     */
    public function end_break(): RedirectResponse
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->first();

        BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('end_break')
            ->update([
                'end_break' => now(),
            ]);

        return redirect('/attendance');
    }

    /**
     * Show the monthly attendance record for staff.
     *
     * @param Request $request
     * @return View
     */
    public function monthlyList(Request $request): View
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

    /**
     * Show the detail of the attendance record for staff.
     *
     * @param int $attendance_id
     * @return View
     */
    public function detail(
        int $attendance_id
    ): View {
        $attendance = Attendance::with([
            'user',
            'breakTimes',
            'attendanceCorrectRequests.breakCorrectRequests',
        ])->findOrFail($attendance_id);

        $breakTimes = $attendance->breakTimes;

        $attendanceCorrectRequest = $attendance->attendanceCorrectRequests
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

    /**
     * Create the correct request for staff.
     *
     * @param AttendanceRequest $request
     * @param int $attendance_id
     * @return RedirectResponse
     */
    public function store(
        AttendanceRequest $request, int $attendance_id
    ): RedirectResponse {
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

    /**
     * Show the correct requests list for staff.
     *
     * @param Request $request
     * @return View
     */
    public function correctRequestList(Request $request): View
    {
        $tab = $request->query('tab');

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

        return view(
            'staff.staff_correct_request_list',
            compact('attendanceCorrectRequests')
        );
    }

    /**
     * Show the attendance report for staff.
     *
     * @return View
     */
    public function report(): View
    {
        // 6か月分の空データ作成
        $baseMonthlyTotals = collect(range(5, 0))->mapWithKeys(function ($i) {
            $month = now()->subMonths($i)->format('Y-m');

            return [
                $month => [
                    'work_minutes' => 0,
                    'overtime_minutes' => 0,
                    'work_days' => 0,
                    'work_time' => '0h 0m',
                    'overtime_time' => '0h 0m',
                ],
            ];
        });

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', auth()->id())
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->get();

        // 実際の勤怠
        $actualMonthlyTotals = $attendances
            ->groupBy(function ($attendance) {
                return $attendance->date->format('Y-m');
            })
            ->map(function ($attendances) {
                // 出退勤が終了している日数のみ抽出
                $completedAttendances = $attendances->filter(function ($attendance) {
                    return $attendance->clock_in && $attendance->clock_out;
                });

                $workMinutes = $completedAttendances->sum(function ($attendance) {
                    return $attendance->work_minutes;
                });

                $workDays = $completedAttendances->count();

                $overtimeMinutes = $completedAttendances->sum(function ($attendance) {
                    return max(
                        0,
                        $attendance->work_minutes - 8 * 60
                    );
                });

                return [
                    'work_minutes' => $workMinutes,
                    'overtime_minutes' => $overtimeMinutes,
                    'work_days' => $workDays,
                    'work_time' => sprintf(
                        '%dh %dm',
                        floor($workMinutes / 60),
                        $workMinutes % 60
                    ),
                    'overtime_time' => sprintf(
                        '%dh %dm',
                        floor($overtimeMinutes / 60),
                        $overtimeMinutes % 60
                    ),
                ];
            });

        // 基本サマリー
        $monthlyTotals = $baseMonthlyTotals->merge($actualMonthlyTotals)->sortKeys();
        // 総労働時間（6ヵ月）
        $totalWorkMinutes = $monthlyTotals->sum('work_minutes');
        // 総残業時間（6ヵ月）
        $totalOvertimeMinutes = $monthlyTotals->sum('overtime_minutes');
        // 総出勤日数（6ヵ月）
        $totalWorkDays = $monthlyTotals->sum('work_days');
        // 平均労働時間（6ヵ月から算出）/日
        $averageWorkMinutes = $totalWorkDays > 0
            ? floor($totalWorkMinutes / $totalWorkDays)
            : 0;

        $summary = [
            'total_work_time' => sprintf('%dh %dm', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60),
            'total_overtime_time' => sprintf('%dh %dm', floor($totalOvertimeMinutes / 60), $totalOvertimeMinutes % 60),
            'average_work_time' => sprintf('%dh %dm', floor($averageWorkMinutes / 60), $averageWorkMinutes % 60),
        ];

        // 今月の異常検知
        $thisMonthAttendances = $attendances->filter(function ($attendance) {
            return $attendance->date->between(
                now()->startOfMonth(),
                now()->endOfMonth()
            );
        });

        // 出退勤が終了している日数のみ抽出
        $completedThisMonthAttendances = $thisMonthAttendances->filter(function ($attendance) {
            return $attendance->clock_in && $attendance->clock_out;
        });

        // 遅刻（9:00より後）
        $lateCount = $completedThisMonthAttendances
            ->filter(function ($attendance) {
                return $attendance->clock_in->format('H:i') > '09:00';
            })
            ->count();

        // 早退（18:00より前）
        $earlyLeaveCount = $completedThisMonthAttendances
            ->filter(function ($attendance) {
                return $attendance->clock_out->format('H:i') < '18:00';
            })
            ->count();

        // 長時間労働（10時間より長い）
        $longWorkCount = $completedThisMonthAttendances
            ->filter(function ($attendance) {
                return $attendance->work_minutes > 10 * 60;
            })
            ->count();

        return view('staff.staff_report', compact(
            'monthlyTotals',
            'summary',
            'lateCount',
            'earlyLeaveCount',
            'longWorkCount',
        ));
    }
}

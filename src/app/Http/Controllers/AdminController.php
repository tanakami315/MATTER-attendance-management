<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{
    /**
     * Show the login page for admin.
     *
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        
            return redirect('/admin/login');
        }
        return view('auth.admin_login');
    }

    /**
     * Show the daily attendance record for admin.
     *
     * @param Request $request
     * @return View
     */
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

    /**
     * Show the detail of the attendance record for admin.
     *
     * @param int $admin_id
     * @return View
     */
    public function adminDetail($admin_id)
    {
        $attendance = Attendance::with([
            'user',
            'breakTimes',
            'attendanceCorrectRequests.breakCorrectRequests',
        ])->findOrFail($admin_id);

        $breakTimes = $attendance->breakTimes;

        $attendanceCorrectRequest = $attendance-> attendanceCorrectRequests
            ->sortByDesc('created_at')
            ->first();
        
        $breakCorrectRequests = $attendanceCorrectRequest
            ? $attendanceCorrectRequest->breakCorrectRequests
            : collect();

        return view(
            'admin.admin_detail',
            compact(
                'attendance',
                'breakTimes',
                'attendanceCorrectRequest',
                'breakCorrectRequests'
            )
        );
    }

    /**
     * Update the attendance record for admin.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateAttendance(AttendanceRequest $request, $attendance_id)
    {
        $attendance = Attendance::findOrFail($attendance_id);
        
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

    /**
     * Show the staff list for admin.
     *
     * @return View
     */
    public function adminStaffList()
    {
        $users = User::where('admin_status',0)->get();
        return view('admin.admin_staff_list', compact('users'));
    }

    /**
     * Show the personal monthly attendance record for admin.
     *
     * @param Request $request
     * @return View
     */
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

    /**
     * Show the correct requests list for admin.
     *
     * @param Request $request
     * @return View
     */
    public function correctRequestList(Request $request)
    {
        $tab = $request->query('tab');

        $user = auth()->user();

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

    /**
     * Show the detail of correct requests for admin.
     *
     * @param Request $request
     * @return View
     */
    public function adminRequestDetail($admin_correct_request_id)
    {
        $attendanceCorrectRequest = AttendanceCorrectRequest::with(
            'attendance.user',
            'breakCorrectRequests'
            )
            ->findOrFail($admin_correct_request_id);
        
        return view(
            'admin.admin_request_detail',
            compact(
                'attendanceCorrectRequest'
            )
        );
    }

    /**
     * Approve the correct request for admin.
     *
     * @param int $attendance_correct_request_id
     * @return RedirectResponse
     */
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

    /**
     * Export the personal monthly attendance record to csv for admin.
     *
     * @param int $user_id
     * @param Request $request
     * @return StreamedResponse
     */
    public function export(int $user_id, Request $request): StreamedResponse
    {
        $month = Carbon::parse($request->month ?? now()->format('Y-m'));

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

        $fileName = $user->name . '_' . $month->format('Y-m') . '_attendance.csv';

        return new StreamedResponse(function () use ($dates, $attendances) {
            $handle = fopen('php://output', 'w');

            $header = ['日付', '出勤', '退勤', '休憩', '合計'];
            mb_convert_variables('SJIS-win', 'UTF-8', $header);
            fputcsv($handle, $header);

            foreach ($dates as $date) {
                $attendance = $attendances->get($date->format('Y-m-d'));

                $row = [
                    $date->format('Y/m/d'),
                    $attendance?->clock_in?->format('H:i') ?? '',
                    $attendance?->clock_out?->format('H:i') ?? '',
                    $attendance?->break_time ?? '',
                    $attendance?->work_time ?? '',
                ];

                mb_convert_variables('SJIS-win', 'UTF-8', $row);
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

}

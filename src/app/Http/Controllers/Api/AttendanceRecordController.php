<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceRecordController extends Controller
{
    // 勤怠一覧を取得
    public function index(IndexAttendanceRecordRequest $request)
    {
        $perPage = min((int) $request->query('per_page', 20), 100);

        $query = Attendance::with('user', 'breakTimes')
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->user_id);
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('date', $request->date);
            })
            ->when($request->filled('month'), function ($query) use ($request) {
                $month = Carbon::parse($request->month);

                $query->whereBetween('date', [
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth(),
                ]);
            });

        $attendanceRecords = $query
            ->latest('date')
            ->paginate($perPage);

        return AttendanceRecordResource::collection($attendanceRecords);
    }

    public function show(Attendance $attendanceRecord)
    {
        $attendanceRecord->load(
            'user',
            'breakTimes',
            'attendanceCorrectRequests.breakCorrectRequests'
        );

        return new AttendanceRecordResource($attendanceRecord);
    }

}

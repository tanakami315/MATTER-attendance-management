<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;
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

    // 勤怠詳細を取得
    public function show(Attendance $attendanceRecord)
    {
        $attendanceRecord->load(
            'user',
            'breakTimes',
            'attendanceCorrectRequests.breakCorrectRequests'
        );

        return new AttendanceRecordResource($attendanceRecord);
    }
    
    // 勤怠登録
    public function store(StoreAttendanceRecordRequest $request)
    {
        $attendance = Attendance::create([
            'user_id' => auth()->id(),
            'date' => $request->date,
            'clock_in' => Carbon::parse($request->date . ' ' . $request->clock_in),
            'clock_out' => $request->clock_out
                ? Carbon::parse($request->date . ' ' . $request->clock_out)
                : null,
            'comment' => $request->comment,
        ]);

        $attendance->load('user', 'breakTimes');

        return (new AttendanceRecordResource($attendance))
            ->response()
            ->setStatusCode(201);
    }
    
    // 勤怠更新
    public function update(UpdateAttendanceRecordRequest $request, $attendanceRecord)
    {
        $attendance = Attendance::find($attendanceRecord);

        if (! $attendance) {
            return response()->json([
                'message' => '勤怠情報が見つかりませんでした。'
            ], 404);
        }

        $this->authorize('update', $attendance);

        $attendance->update([
            'date' => $request->date,
            'clock_in' => Carbon::parse($request->date . ' ' . $request->clock_in),
            'clock_out' => $request->clock_out
                ? Carbon::parse($request->date . ' ' . $request->clock_out)
                : null,
            'comment' => $request->comment,
        ]);

        $attendance->load('user', 'breakTimes');

        return new AttendanceRecordResource($attendance);
    }

    // 勤怠削除
    public function destroy($attendanceRecord)
    {
        $attendance = Attendance::find($attendanceRecord);

        if (! $attendance) {
            return response()->json([
                'message' => '勤怠情報が見つかりませんでした。'
            ], 404);
        }

        $this->authorize('delete', $attendance);

        $attendance->delete();

        return response('', 204);
    }
}
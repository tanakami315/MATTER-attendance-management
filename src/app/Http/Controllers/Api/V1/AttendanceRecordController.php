<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceRecordResource;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AttendanceRecordController extends Controller
{
    /**
     * Show the daily attendance record for API.
     *
     * @param IndexAttendanceRecordRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(
        IndexAttendanceRecordRequest $request
    ): AnonymousResourceCollection {
        $perPage = min((int) $request->query('per_page', 20), 100);

        $query = Attendance::with('user')
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

    /**
     * Show the detail of the attendance record for API.
     *
     * @param int $attendanceRecord
     * @return AttendanceRecordResource
     */
    public function show(
        int $attendanceRecord
    ): AttendanceRecordResource {
        $attendance = Attendance::with(
            'user',
            'breakTimes',
            'attendanceCorrectRequests.breakCorrectRequests'
        )->findOrFail($attendanceRecord);

        return new AttendanceRecordResource($attendance);
    }

    /**
     * Create the attendance record for API.
     *
     * @param StoreAttendanceRecordRequest $request
     * @return JsonResponse
     */
    public function store(
        StoreAttendanceRecordRequest $request
    ): JsonResponse {
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

    /**
     * Update the attendance record for API.
     *
     * @param UpdateAttendanceRecordRequest $request
     * @param int $attendanceRecord
     * @return AttendanceRecordResource
     */
    public function update(
        UpdateAttendanceRecordRequest $request,
        int $attendanceRecord
    ): AttendanceRecordResource {
        $attendance = Attendance::findOrFail($attendanceRecord);

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

    /**
     * Delete the attendance record for API.
     *
     * @param int $attendanceRecord
     * @return Response
     */
    public function destroy(
        int $attendanceRecord
    ): Response {
        $attendance = Attendance::findOrFail($attendanceRecord);

        $this->authorize('delete', $attendance);

        $attendance->delete();

        return response('', 204);
    }
}
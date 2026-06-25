<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user->name,
            'date' => $this->date->format('Y-m-d'),
            'clock_in' => $this->clock_in?->format('H:i'),
            'clock_out' => $this->clock_out?->format('H:i'),
            'break_time' => $this->break_time,
            'work_time' => $this->work_time,

            'correction_requests' => $this->attendanceCorrectRequests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'clock_in' => $request->clock_in?->format('H:i'),
                    'clock_out' => $request->clock_out?->format('H:i'),
                    'comment' => $request->comment,
                    'status' => $request->status,
                    'breaks' => $request->breakCorrectRequests->map(function ($break) {
                        return [
                            'start_break' => $break->start_break?->format('H:i'),
                            'end_break' => $break->end_break?->format('H:i'),
                        ];
                    }),
                ];
            }),
        ];
    }
}

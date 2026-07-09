<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'date' => $this->date?->format('Y-m-d'),
            'clock_in' => $this->clock_in?->format('H:i'),
            'clock_out' => $this->clock_out?->format('H:i'),
            'work_time' => $this->work_time,
            'break_time' => $this->break_time,
            'comment' => $this->comment,

            'breakTimes' => $this->when(
                $request->routeIs('attendance-records.show'),
                function () {
                    return BreakTimeResource::collection($this->breakTimes);
                }
            ),

            'attendanceCorrectRequests' => AttendanceCorrectRequestResource::collection(
                $this->whenLoaded('attendanceCorrectRequests')
            ),
        ];
    }
}

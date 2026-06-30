<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceCorrectRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'clock_in' => $this->clock_in?->format('H:i'),
            'clock_out' => $this->clock_out?->format('H:i'),
            'comment' => $this->comment,
            'status' => $this->status,
        ];
    }
}

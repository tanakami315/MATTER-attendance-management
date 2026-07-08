<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakCorrectRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correct_request_id',
        'start_break',
        'end_break',
    ];

    protected $casts = [
        'start_break' => 'datetime',
        'end_break' => 'datetime',
    ];

    /**
     * Get the attendance correction request that owns the break correction request.
     *
     * @return BelongsTo
     */
    public function attendanceCorrectRequest(): BelongsTo
    {
        return $this->belongsTo(AttendanceCorrectRequest::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'comment',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];
    
    /**
     * Get the user who owns the attendance.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the break times for the attendance.
     *
     * @return HasMany
     */
    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    /**
     * Get the attendance correction requests.
     *
     * @return HasMany
     */
    public function attendanceCorrectRequests()
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }

    /**
     * Get the total break time in minutes.
     *
     * @return int
     */
    public function getBreakMinutesAttribute()
    {
        return $this->breakTimes()->get()->sum(function ($breakTime) {
            if (!$breakTime->start_break || !$breakTime->end_break) {
                return 0;
            }

            return $breakTime->start_break->diffInMinutes($breakTime->end_break);
        });
    }

    /**
     * Get the total work time in minutes.
     *
     * @return int
     */
    public function getWorkMinutesAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        return $this->clock_in->diffInMinutes($this->clock_out)
            - $this->break_minutes;
    }

    /**
     * Get the formatted break time.
     *
     * @return string
     */
    public function getBreakTimeAttribute()
    {
        $minutes = $this->break_minutes;

        return sprintf(
            '%d:%02d',
            floor($minutes / 60),
            $minutes % 60
        );
    }

    /**
     * Get the formatted work time.
     *
     * @return string
     */
    public function getWorkTimeAttribute()
    {
        $minutes = $this->work_minutes;

        return sprintf(
            '%d:%02d',
            floor($minutes / 60),
            $minutes % 60
        );
    }
}

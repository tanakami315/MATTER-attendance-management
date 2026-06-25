<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BreakTime;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function attendanceCorrectRequests()
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }

    // 休憩時間計算(分)
    public function getBreakMinutesAttribute()
    {
        return $this->breakTimes->sum(function ($breakTime) {
            if (!$breakTime->start_break || !$breakTime->end_break) {
                return 0;
            }

            return $breakTime->start_break->diffInMinutes($breakTime->end_break);
        });
    }

    // 勤務時間計算(分)
    public function getWorkMinutesAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        return $this->clock_in->diffInMinutes($this->clock_out)
            - $this->break_minutes;
    }

    // 休憩時間計算(時間:分)
    public function getBreakTimeAttribute()
    {
        $minutes = $this->break_minutes;

        return sprintf(
            '%d:%02d',
            floor($minutes / 60),
            $minutes % 60
        );
    }

    // 勤務時間計算(時間:分)
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

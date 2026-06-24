<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attendance;
use App\Models\BreakCorrectRequest;

class AttendanceCorrectRequest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'attendance_id',
        'clock_in',
        'clock_out',
        'comment',
        'status',
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function breakCorrectRequests()
    {
        return $this->hasMany(BreakCorrectRequest::class);
    }
}

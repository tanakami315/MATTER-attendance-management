<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;
    protected $table = 'breaks';
    protected $fillable = ['attendance_id', 'start_break', 'end_break'];
    protected $casts = [
        'start_break' => 'datetime',
        'end_break' => 'datetime',
    ];
    public function attendance()
    {
        return $this->belongsToMany(Attendance::class);
    }
}

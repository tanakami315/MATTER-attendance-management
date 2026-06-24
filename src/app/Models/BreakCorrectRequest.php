<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}

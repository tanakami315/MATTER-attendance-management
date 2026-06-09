<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationBreak extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'application_id',
        'start_break',
        'end_break',
        'status',
    ];
}

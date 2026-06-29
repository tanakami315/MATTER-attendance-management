<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BreakTime extends Model
{
    use HasFactory;
    protected $table = 'breaks';
    protected $fillable = ['attendance_id', 'start_break', 'end_break'];
    protected $casts = [
        'start_break' => 'datetime',
        'end_break' => 'datetime',
    ];

    /**
     * Get the attendance which has the break times.
     *
     * @return BelongsTo
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}

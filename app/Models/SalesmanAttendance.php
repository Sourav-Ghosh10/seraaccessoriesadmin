<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesmanAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'date',
        'clock_in_time',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_in_address',
        'clock_out_time',
        'clock_out_latitude',
        'clock_out_longitude',
        'clock_out_address',
        'total_hours',
        'clockout_type',
        'is_unlocked',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function getTotalHoursAttribute($value)
    {
        if ($this->clock_in_time && $this->clock_out_time) {
            $diffInSeconds = abs(round($this->clock_out_time->diffInSeconds($this->clock_in_time)));
            $hours = floor($diffInSeconds / 3600);
            $mins = floor(($diffInSeconds % 3600) / 60);
            return sprintf('%02d:%02d', $hours, $mins);
        }
        return $value;
    }
}

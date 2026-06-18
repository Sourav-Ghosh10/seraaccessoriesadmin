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
}

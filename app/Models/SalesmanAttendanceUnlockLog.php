<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesmanAttendanceUnlockLog extends Model
{
    protected $fillable = [
        'attendance_id',
        'salesman_id',
        'locked_at',
        'unlocked_at',
        'unlocked_by',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
        'unlocked_at' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(SalesmanAttendance::class, 'attendance_id');
    }

    public function salesman()
    {
        return $this->belongsTo(Member::class, 'salesman_id');
    }

    public function admin()
    {
        return $this->belongsTo(Member::class, 'unlocked_by');
    }
}

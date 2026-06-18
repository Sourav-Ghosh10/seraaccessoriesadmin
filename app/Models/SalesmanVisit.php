<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesmanVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'salesman_id',
        'dealer_id',
        'visit_time',
        'latitude',
        'longitude',
        'address',
        'notes',
        'photo_path',
    ];

    protected $casts = [
        'visit_time' => 'datetime',
    ];

    public function salesman()
    {
        return $this->belongsTo(Member::class, 'salesman_id');
    }

    public function dealer()
    {
        return $this->belongsTo(Member::class, 'dealer_id');
    }
}

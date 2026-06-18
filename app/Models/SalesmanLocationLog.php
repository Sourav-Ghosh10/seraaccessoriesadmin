<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesmanLocationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'salesman_id',
        'latitude',
        'longitude',
        'timestamp',
        'battery_level',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function salesman()
    {
        return $this->belongsTo(Member::class, 'salesman_id');
    }
}

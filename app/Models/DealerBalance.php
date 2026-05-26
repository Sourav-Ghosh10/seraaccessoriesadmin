<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealerBalance extends Model
{
    protected $fillable = [
        'member_id',
        'total_amount',
        'paid_amount',
        'due_amount',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardTransaction extends Model
{
    protected $fillable = ['member_id', 'order_id', 'points', 'type', 'unlock_days', 'count_days'];

    protected $casts = [
        'points' => 'integer',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

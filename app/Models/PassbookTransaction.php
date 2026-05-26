<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PassbookTransaction extends Model
{
    protected $fillable = [
        'member_id',
        'managed_by',
        'type',
        'amount',
        'ref',
        'status',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}

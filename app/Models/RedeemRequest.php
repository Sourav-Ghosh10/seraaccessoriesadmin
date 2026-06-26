<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedeemRequest extends Model
{
    protected $table = 'redeem_request';

    protected $fillable = [
        'member_id',
        'Points',
        'Credit_note',
        'notes',
        'status',
        'dealer_file_path',
        'distributor_file_path',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}


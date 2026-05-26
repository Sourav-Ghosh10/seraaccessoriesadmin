<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSubmission extends Model
{
    protected $fillable = ['member_id', 'amount', 'receipt_path', 'status', 'remarks'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}

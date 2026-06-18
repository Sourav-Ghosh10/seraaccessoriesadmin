<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'member_id', 'distributor_id', 'order_number', 'type', 'description', 'challan_file', 'invoice_file', 'amount', 'status', 'received_at'
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function member() {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function distributor() {
        return $this->belongsTo(Member::class, 'distributor_id');
    }

    public function delivery() {
        return $this->hasOne(Delivery::class);
    }

    public function invoice() {
        return $this->hasOne(Invoice::class);
    }

    public function creditNote() {
        return $this->hasOne(CreditNote::class);
    }

    public function items() {
        return $this->hasMany(OrderItem::class);
    }

    public function rewardTransactions() {
        return $this->hasMany(RewardTransaction::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = ['order_id', 'vehicle_no', 'vehicle_type', 'driver_phone', 'expected_delivery_at', 'remarks', 'status'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

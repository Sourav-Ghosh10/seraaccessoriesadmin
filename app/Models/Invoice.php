<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['invoice_number', 'order_id', 'amount', 'file_path'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

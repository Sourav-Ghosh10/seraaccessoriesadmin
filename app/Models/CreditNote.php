<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    protected $fillable = ['credit_note_number', 'order_id', 'amount', 'file_path', 'note', 'dealer_file_path', 'distributor_file_path'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

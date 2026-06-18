<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'salesman_id',
        'expense_category_id',
        'amount',
        'description',
        'receipt_photo_path',
        'status',
    ];

    public function salesman()
    {
        return $this->belongsTo(Member::class, 'salesman_id');
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }
}

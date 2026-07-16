<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reimbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'salesman_id',
        'amount',
        'document_path',
        'description',
        'status',
    ];

    public function salesman()
    {
        return $this->belongsTo(Member::class, 'salesman_id');
    }
}

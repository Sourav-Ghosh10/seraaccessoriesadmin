<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRequest extends Model
{
    protected $fillable = ['member_id', 'request_number', 'type', 'description', 'file_path', 'status'];

    protected $casts = [
        'file_path' => 'array',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}

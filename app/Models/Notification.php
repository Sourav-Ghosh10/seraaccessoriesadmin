<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['member_id', 'title', 'body', 'data', 'is_read'];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean'
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}

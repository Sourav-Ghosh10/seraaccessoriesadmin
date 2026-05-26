<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estimate extends Model
{
    protected $fillable = ['member_id', 'request_number', 'type', 'description', 'file_path', 'status', 'response_description', 'response_file_path'];

    protected $casts = [
        'file_path' => 'array',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}

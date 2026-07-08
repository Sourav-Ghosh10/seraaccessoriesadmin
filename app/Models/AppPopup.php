<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppPopup extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}

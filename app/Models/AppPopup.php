<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppPopup extends Model
{
    protected $fillable = [
        'banner_image',
        'image',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}

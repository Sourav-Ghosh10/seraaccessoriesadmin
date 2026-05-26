<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberDevice extends Model
{
    protected $fillable = ['member_id', 'fcm_token'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}

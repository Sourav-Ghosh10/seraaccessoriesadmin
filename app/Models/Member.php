<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Member extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'mobile', 'password', 'role', 'status',
        'shop', 'address', 'salesman_id', 'emp_id', 'ref_code'
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function salesman()
    {
        return $this->belongsTo(Member::class, 'salesman_id');
    }

    public function dealers()
    {
        return $this->hasMany(Member::class, 'salesman_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'member_id');
    }

    public function estimates()
    {
        return $this->hasMany(Estimate::class, 'member_id');
    }

    public function devices()
    {
        return $this->hasMany(MemberDevice::class, 'member_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'member_id');
    }

    public function rewardTransactions()
    {
        return $this->hasMany(RewardTransaction::class, 'member_id');
    }

    public function getPointsBalanceAttribute()
    {
        return $this->rewardTransactions()->sum('points');
    }

    public function dealerBalance()
    {
        return $this->hasOne(DealerBalance::class, 'member_id');
    }

    public function passbookTransactions()
    {
        return $this->hasMany(PassbookTransaction::class, 'member_id');
    }

    public function paymentSubmissions()
    {
        return $this->hasMany(PaymentSubmission::class, 'member_id');
    }
}

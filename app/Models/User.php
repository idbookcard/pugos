<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'balance',
        'gift_balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'balance' => 'decimal:2',
        'gift_balance' => 'decimal:2',
    ];

    /**
     * 获取总余额
     */
    public function getTotalBalanceAttribute()
    {
        return $this->balance + $this->gift_balance;
    }

    /**
     * 用户个人资料
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * 用户订单
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * 用户交易
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * 钱包交易记录
     */
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * 用户发票
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
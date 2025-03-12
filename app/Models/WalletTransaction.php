<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'balance_type',
        'related_id',
        'description',
        'before_balance',
        'after_balance',
    ];

    /**
     * 应该转换的属性
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'before_balance' => 'decimal:2',
        'after_balance' => 'decimal:2',
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取关联的订单（如果是订单消费类型）
     */
    public function order()
    {
        if ($this->type == 'consumption' && $this->related_id) {
            return $this->belongsTo(Order::class, 'related_id');
        }
        return null;
    }

    /**
     * 获取关联的交易（如果是充值类型）
     */
    public function transaction()
    {
        if ($this->type == 'recharge' && $this->related_id) {
            return $this->belongsTo(Transaction::class, 'related_id');
        }
        return null;
    }

    /**
     * 获取交易类型的显示名称
     */
    public function getTypeNameAttribute()
    {
        $types = [
            'recharge' => '充值',
            'consumption' => '消费',
            'refund' => '退款',
            'gift' => '赠送',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * 获取余额类型的显示名称
     */
    public function getBalanceTypeNameAttribute()
    {
        $types = [
            'main' => '主余额',
            'gift' => '赠送余额',
        ];

        return $types[$this->balance_type] ?? $this->balance_type;
    }

    /**
     * 金额是否为正数（收入）
     */
    public function isIncome()
    {
        return $this->amount > 0;
    }

    /**
     * 获取金额的显示字符串
     */
    public function getAmountDisplayAttribute()
    {
        $prefix = $this->amount > 0 ? '+' : '';
        return $prefix . number_format($this->amount, 2);
    }

    /**
     * 获取关联ID的描述
     */
    public function getRelatedDescriptionAttribute()
    {
        if (!$this->related_id) {
            return null;
        }

        if ($this->type == 'consumption') {
            return '订单 #' . $this->related_id;
        } elseif ($this->type == 'recharge') {
            return '交易 #' . $this->related_id;
        }

        return '关联ID: ' . $this->related_id;
    }
}
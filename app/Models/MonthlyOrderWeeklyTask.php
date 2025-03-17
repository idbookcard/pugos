<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyOrderWeeklyTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'week_number',
        'target_url',
        'keywords',
        'description',
        'status',
        'completed_at',
        'work_order_number',
        'work_order_created_at',
        'work_order_status',
        'work_order_assignee'
    ];

    /**
     * 日期字段应转换为Carbon实例
     */
    protected $dates = [
        'completed_at',
        'work_order_created_at',
        'created_at',
        'updated_at'
    ];

    /**
     * 获取此任务关联的订单
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
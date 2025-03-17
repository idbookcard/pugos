<?php
// app/Models/Order.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'package_id', 'external_service_id', 'guest_post_site_id',
        'order_number', 'service_type', 'status', 'payment_status',
        'refund_status', 'total_amount', 'third_party_order_id',
        'target_url', 'keywords', 'article', 'extra_data'
    ];

    protected $casts = [
        'extra_data' => 'array',
        'total_amount' => 'decimal:2',
        'order_date' => 'datetime',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function externalService()
    {
        return $this->belongsTo(ExternalService::class);
    }

    public function guestPostSite()
    {
        return $this->belongsTo(GuestPostSite::class);
    }

    public function reports()
    {
        return $this->hasMany(OrderReport::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

     /**
     * 获取包月订单详情
     */
    public function monthlyDetail()
    {
        return $this->hasOne(MonthlyOrderDetail::class);
    }
    
    /**
     * 获取包月订单的每周任务
     */
    public function weeklyTasks()
    {
        return $this->hasMany(MonthlyOrderWeeklyTask::class);
    }
     /**
     * 判断是否为包月订单
     */
    public function isMonthly()
    {
        return $this->service_type === 'monthly';
    }

}
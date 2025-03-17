<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiOrder extends Model
{
    use HasFactory;

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'api_order_id',
        'api_status',
        'api_response',
        'submitted_at',
        'completed_at',
    ];

    /**
     * 应该转换的属性
     *
     * @var array
     */
    protected $casts = [
        'api_response' => 'array',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * 获取关联的订单
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * 判断API订单是否已提交
     */
    public function isSubmitted()
    {
        return !empty($this->api_order_id) && !empty($this->submitted_at);
    }

    /**
     * 判断API订单是否已完成
     */
    public function isCompleted()
    {
        return !empty($this->completed_at);
    }

    /**
     * 获取状态的显示名称
     */
    public function getStatusNameAttribute()
    {
        if (!$this->isSubmitted()) {
            return '未提交';
        }

        $statuses = [
            'submitted' => '已提交',
            'processing' => '处理中',
            'completed' => '已完成',
            'failed' => '失败',
            'canceled' => '已取消',
        ];

        return $statuses[$this->api_status] ?? $this->api_status;
    }

    /**
     * 获取API状态显示样式类
     */
    public function getStatusClassAttribute()
    {
        if (!$this->isSubmitted()) {
            return 'secondary';
        }

        $classes = [
            'submitted' => 'info',
            'processing' => 'primary',
            'completed' => 'success',
            'failed' => 'danger',
            'canceled' => 'warning',
        ];

        return $classes[$this->api_status] ?? 'secondary';
    }

    /**
     * 获取报告URL（如果有）
     */
    public function getReportUrlAttribute()
    {
        if (empty($this->api_response)) {
            return null;
        }

        // 尝试从API响应中获取报告URL
        if (isset($this->api_response['data']['report'])) {
            return $this->api_response['data']['report'];
        }

        if (isset($this->api_response['report'])) {
            return $this->api_response['report'];
        }

        return null;
    }

    /**
     * 获取API订单在第三方平台的URL（如果有）
     */
    public function getApiOrderUrlAttribute()
    {
        if (empty($this->api_order_id)) {
            return null;
        }

        // 这里可以根据你集成的API服务定制URL
        // 例如SEOeStore的订单查看URL
        $apiBaseUrl = config('services.seoestore.dashboard_url', 'https://seoestore.com/dashboard');
        return $apiBaseUrl . '/orders/' . $this->api_order_id;
    }

    /**
     * 处理提交失败
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->api_status = 'failed';
        $this->api_response = array_merge($this->api_response ?? [], [
            'error' => $errorMessage,
            'failed_at' => now()->toDateTimeString()
        ]);
        $this->save();
    }
}
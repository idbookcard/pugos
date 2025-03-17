<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyOrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'company_name',
        'website',
        'phone',
        'address',
        'contact_email',
        'industry',
        'business_hours',
        'contact_name',
        'description',
        'services_keywords',
        'social_media',
        'article_file_path'
    ];

    /**
     * 获取此详情关联的订单
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
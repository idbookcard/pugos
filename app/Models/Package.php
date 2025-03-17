<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'third_party_id',
        'guest_post_da',
        'name',
        'name_en',
        'slug',
        'description',
        'description_zh',
        'features',
        'available_extras', // 添加这个字段
        'price',
        'original_price',
        'delivery_days',
        'package_type',
        'is_featured',
        'active',
        'sort_order',
        'is_api_product',
        'min_quantity', // 可能也需要添加
        'is_contextual',  // 可能也需要添加
        'weekly_tasks_template'
    ];

    protected $casts = [
        'features' => 'array',
        'available_extras' => 'array', // 添加这个类型转换
        'price' => 'decimal:7',
        'original_price' => 'decimal:7',
        'is_featured' => 'boolean',
        'active' => 'boolean',
        'is_api_product' => 'boolean',
        'is_contextual' => 'boolean', // 可能也需要添加
        'weekly_tasks_template' => 'array'
    ];

    /**
     * 获取套餐分类
     */
    public function category()
    {
        return $this->belongsTo(PackageCategory::class, 'category_id');
    }

    /**
     * 获取套餐下的订单
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'package_id');
    }

    /**
     * 判断是否为第三方API套餐
     */
    public function isThirdParty()
    {
        return $this->package_type === 'third_party';
    }

    /**
     * 判断是否为Guest Post套餐
     */
    public function isGuestPost()
    {
        return $this->package_type === 'guest_post';
    }

    /**
     * 判断是否为包月套餐
     */
    public function isMonthly()
    {
        return $this->package_type === 'monthly';
    }

    /**
     * 获取折扣百分比
     */
    public function getDiscountPercentageAttribute()
    {
        if (!$this->original_price || $this->original_price <= $this->price) {
            return 0;
        }

        return round((1 - ($this->price / $this->original_price)) * 100);
    }


    // 在 Package 模型中添加
public function getFormattedPriceAttribute()
{
    $price = $this->price;
    if ($price == 0) {
        return '0';
    }
    
    if ($price == (int)$price) {
        return number_format($price, 0);
    }
    
    return number_format($price, 2);
}

public function getFormattedOriginalPriceAttribute()
{
    $price = $this->original_price;
    if ($price == 0) {
        return '0';
    }
    
    if ($price == (int)$price) {
        return number_format($price, 0);
    }
    
    return number_format($price, 2);
}
}
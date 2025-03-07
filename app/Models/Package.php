<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'category_id',
        'name',
        'name_en',
        'slug',
        'description',
        'description_zh',
        'features',
        'price',
        'original_price',
        'delivery_days',
        'package_type',
        'is_featured',
        'active',
        'sort_order',
        'third_party_id',
        'guest_post_da'
    ];
    
    protected $casts = [
        'features' => 'array',
        'is_featured' => 'boolean',
        'active' => 'boolean'
    ];
    
    /**
     * 获取套餐所属分类
     */
    public function category()
    {
        return $this->belongsTo(PackageCategory::class);
    }
    
    /**
     * 获取套餐的所有订单
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    /**
     * 月度套餐范围查询
     */
    public function scopeMonthly($query)
    {
        return $query->where('package_type', 'monthly')->where('active', 1);
    }
    
    /**
     * 单项套餐范围查询
     */
    public function scopeSingle($query)
    {
        return $query->where('package_type', 'single')->where('active', 1);
    }
    
    /**
     * 第三方套餐范围查询
     */
    public function scopeThirdParty($query)
    {
        return $query->where('package_type', 'third_party')->where('active', 1);
    }
    
    /**
     * 软文外链范围查询
     */
    public function scopeGuestPost($query)
    {
        return $query->where('package_type', 'guest_post')->where('active', 1);
    }
    
    /**
     * 推荐套餐范围查询
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', 1)->where('active', 1);
    }
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraOption extends Model
{
    use HasFactory;
    
    /**
     * 允许批量赋值的属性
     */
    protected $fillable = [
        'extra_id',
        'code',
        'name',
        'name_zh',
        'price',
        'is_multiple',
        'active'
    ];
    
    /**
     * 应该被转换为原生类型的属性
     */
    protected $casts = [
        'price' => 'float',
        'is_multiple' => 'boolean',
        'active' => 'boolean',
    ];
    
    /**
     * 获取关联的产品
     */
    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_extra_options');
    }
}
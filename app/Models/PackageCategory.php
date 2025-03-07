<?php
// app/Models/PackageCategory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageCategory extends Model
{
    protected $fillable = [
        'name', 'name_en', 'slug', 'description', 'description_zh',
        'sort_order', 'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function packages()
    {
        return $this->hasMany(Package::class, 'category_id');
    }
}
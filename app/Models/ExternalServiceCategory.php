<?php
// app/Models/ExternalServiceCategory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalServiceCategory extends Model
{
    protected $fillable = [
        'name', 'name_en', 'slug', 'description',
        'sort_order', 'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function services()
    {
        return $this->hasMany(ExternalService::class, 'category_id');
    }
}
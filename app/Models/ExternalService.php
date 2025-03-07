<?php// app/Models/ExternalService.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalService extends Model
{
    protected $fillable = [
        'category_id', 'external_id', 'name', 'description',
        'price', 'delivery_days', 'required_fields', 'extras', 'active'
    ];

    protected $casts = [
        'required_fields' => 'array',
        'extras' => 'array',
        'price' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ExternalServiceCategory::class, 'category_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'external_service_id');
    }
}
<?php
// app/Models/GuestPostSite.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestPostSite extends Model
{
    protected $fillable = [
        'category_id', 'site_id', 'domain', 'title', 'description',
        'requirements', 'price', 'domain_rating', 'traffic',
        'metrics_data', 'active', 'category_slug'
    ];

    protected $casts = [
        'metrics_data' => 'array',
        'price' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(GuestPostCategory::class, 'category_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'guest_post_site_id');
    }
}
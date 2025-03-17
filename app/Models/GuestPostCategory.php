<?php
// app/Models/GuestPostCategory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestPostCategory extends Model
{
    protected $fillable = [
        'name', 'name_en', 'slug', 'description',
        'sort_order', 'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function sites()
    {
        return $this->hasMany(GuestPostSite::class, 'category_id');
    }
}
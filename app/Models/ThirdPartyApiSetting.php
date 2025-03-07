<?php
// app/Models/ThirdPartyApiSetting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThirdPartyApiSetting extends Model
{
    protected $fillable = [
        'name', 'api_key', 'api_secret', 'api_url', 'settings'
    ];

    protected $casts = [
        'settings' => 'array',
    ];
}
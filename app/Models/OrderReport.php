<?php
// app/Models/OrderReport.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReport extends Model
{
    protected $fillable = [
        'order_id', 'status', 'report_data', 'source', 'placed_at'
    ];

    protected $casts = [
        'report_data' => 'array',
        'placed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
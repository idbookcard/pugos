<?php
// app/Models/Transaction.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'order_id', 'transaction_type', 'amount',
        'payment_method', 'payment_details', 'status',
        'reference_id', 'notes'
    ];

    protected $casts = [
        'payment_details' => 'array',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function cryptoPayment()
    {
        return $this->hasOne(CryptoPayment::class);
    }
}
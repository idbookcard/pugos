<?php
// app/Models/CryptoPayment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoPayment extends Model
{
    protected $fillable = [
        'user_id', 'transaction_id', 'currency', 'network',
        'amount', 'amount_usd', 'wallet_address', 'tx_hash',
        'status', 'expires_at', 'confirmed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'amount_usd' => 'decimal:2',
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
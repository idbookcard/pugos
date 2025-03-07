<?php
// app/Models/User.php (updated version with admin role methods)
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'balance' => 'decimal:2',
    ];

    /**
     * Check if user is an admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        $adminEmails = config('pugos.admin_emails', []);
        
        // Check if user's email is in the admin list
        if (in_array($this->email, $adminEmails)) {
            return true;
        }
        
        // Check for admin flag in user's record (could be added for flexibility)
        return (bool) $this->is_admin;
    }

    /**
     * Get user profile
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get user orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get user transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get user invoices
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get user crypto payments
     */
    public function cryptoPayments()
    {
        return $this->hasMany(CryptoPayment::class);
    }

    /**
     * Get only deposit transactions
     */
    public function deposits()
    {
        return $this->transactions()->where('transaction_type', 'deposit');
    }

    /**
     * Get only payment transactions
     */
    public function payments()
    {
        return $this->transactions()->where('transaction_type', 'order_payment');
    }

    /**
     * Format user's name or email for display
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return $this->name ?: $this->email;
    }

    /**
     * Get the formatted balance with currency symbol
     *
     * @return string
     */
    public function getFormattedBalanceAttribute()
    {
        return '¥' . number_format($this->balance, 2);
    }
}
<?php
// app/Models/Invoice.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'invoice_number',
        'invoice_type',
        'title',
        'tax_number',
        'amount',
        'email',
        'address',
        'bank_info',
        'notes',
        'status',
        'rejection_reason',
        'file_path',
        'sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the user that owns the invoice.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted invoice type
     *
     * @return string
     */
    public function getFormattedTypeAttribute()
    {
        $types = [
            'regular' => 'Regular Invoice',
            'vat' => 'VAT Invoice',
        ];

        return $types[$this->invoice_type] ?? ucfirst($this->invoice_type);
    }

    /**
     * Get formatted status
     *
     * @return string
     */
    public function getFormattedStatusAttribute()
    {
        $statuses = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'sent' => 'Sent',
        ];

        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get formatted amount
     *
     * @return string
     */
    public function getFormattedAmountAttribute()
    {
        return '¥' . number_format($this->amount, 2);
    }

    /**
     * Get the invoice status color class
     *
     * @return string
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'approved' => 'primary',
            'rejected' => 'danger',
            'sent' => 'success',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Check if invoice can be downloaded
     *
     * @return bool
     */
    public function getCanDownloadAttribute()
    {
        return $this->status === 'sent' && !empty($this->file_path);
    }

    /**
     * Scope a query to only include pending invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include sent invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope a query to only include rejected invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
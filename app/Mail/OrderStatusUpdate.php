<?php

// app/Mail/OrderStatusUpdate.php
namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdate extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $package;
    public $user;
    public $oldStatus;
    public $statusMessages;

    /**
     * Create a new message instance.
     *
     * @param Order $order
     * @param string $oldStatus
     * @return void
     */
    public function __construct(Order $order, $oldStatus)
    {
        $this->order = $order;
        $this->package = $order->package;
        $this->user = $order->user;
        $this->oldStatus = $oldStatus;
        
        // Define status-specific messages
        $this->statusMessages = [
            'pending' => 'Your order has been received and is awaiting processing.',
            'processing' => 'Your order is now being processed. Our team is working on it.',
            'completed' => 'Good news! Your order has been completed successfully.',
            'canceled' => 'Your order has been canceled.',
            'rejected' => 'Unfortunately, your order has been rejected.',
            'refunded' => 'Your order has been refunded.',
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $statusVerb = [
            'pending' => 'is pending',
            'processing' => 'is now processing',
            'completed' => 'has been completed',
            'canceled' => 'has been canceled',
            'rejected' => 'has been rejected',
            'refunded' => 'has been refunded',
        ];
        
        $currentStatus = $this->order->status;
        $statusText = $statusVerb[$currentStatus] ?? 'has been updated';
        
        $subject = "Order #{$this->order->order_number} $statusText";
        
        return $this->subject($subject)
                    ->markdown('emails.orders.status-update')
                    ->with([
                        'order' => $this->order,
                        'package' => $this->package,
                        'user' => $this->user,
                        'oldStatus' => $this->oldStatus,
                        'statusMessage' => $this->statusMessages[$currentStatus] ?? 'Your order status has been updated.',
                        'orderUrl' => route('customer.orders.show', $this->order),
                        'supportEmail' => config('mail.support_email', 'support@pugos.cn'),
                        'companyName' => config('app.name', 'PuGOS')
                    ]);
    }
}
<?php
// app/Mail/OrderConfirmation.php
namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $package;
    public $user;

    /**
     * Create a new message instance.
     *
     * @param Order $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->package = $order->package;
        $this->user = $order->user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Order Confirmation: #' . $this->order->order_number;
        
        return $this->subject($subject)
                    ->markdown('emails.orders.confirmation')
                    ->with([
                        'order' => $this->order,
                        'package' => $this->package,
                        'user' => $this->user,
                        'orderUrl' => route('customer.orders.show', $this->order),
                        'supportEmail' => config('mail.support_email', 'support@pugos.cn'),
                        'companyName' => config('app.name', 'PuGOS')
                    ]);
    }
}
<?php
// app/Mail/PaymentConfirmation.php
namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $transaction;
    public $user;

    /**
     * Create a new message instance.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
        $this->user = $transaction->user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Payment Confirmation: #' . $this->transaction->reference_id;
        
        $paymentMethod = ucfirst($this->transaction->payment_method);
        
        // Format payment details for display
        $paymentDetails = [];
        if ($this->transaction->payment_details) {
            $details = is_array($this->transaction->payment_details) 
                ? $this->transaction->payment_details 
                : json_decode($this->transaction->payment_details, true);
            
            if (is_array($details)) {
                foreach ($details as $key => $value) {
                    if (in_array($key, ['qr_code', 'payment_url', 'wallet_address', 'crypto_amount', 'currency'])) {
                        $label = str_replace('_', ' ', ucfirst($key));
                        $paymentDetails[$label] = $value;
                    }
                }
            }
        }
        
        return $this->subject($subject)
                    ->markdown('emails.payments.confirmation')
                    ->with([
                        'transaction' => $this->transaction,
                        'user' => $this->user,
                        'paymentMethod' => $paymentMethod,
                        'paymentDetails' => $paymentDetails,
                        'walletUrl' => route('customer.wallet'),
                        'supportEmail' => config('mail.support_email', 'support@pugos.cn'),
                        'companyName' => config('app.name', 'PuGOS')
                    ]);
    }
}
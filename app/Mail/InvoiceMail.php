<?php
// app/Mail/InvoiceMail.php
namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class InvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invoice;
    public $user;
    public $type;
    public $statusMessage;

    /**
     * Create a new message instance.
     *
     * @param Invoice $invoice
     * @param string $type
     * @return void
     */
    public function __construct(Invoice $invoice, $type)
    {
        $this->invoice = $invoice;
        $this->user = $invoice->user;
        $this->type = $type;
        
        // Define status messages
        $this->statusMessage = [
            'approved' => 'Your invoice request has been approved. We are processing your invoice now.',
            'rejected' => 'Unfortunately, your invoice request has been rejected.',
            'sent' => 'Your invoice has been processed and is now available for download.',
        ][$type] ?? 'Your invoice status has been updated.';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Invoice ' . ucfirst($this->type) . ': #' . $this->invoice->invoice_number;
        
        $mail = $this->subject($subject)
                    ->markdown('emails.invoices.status')
                    ->with([
                        'invoice' => $this->invoice,
                        'user' => $this->user,
                        'type' => $this->type,
                        'statusMessage' => $this->statusMessage,
                        'invoiceUrl' => route('customer.invoices.show', $this->invoice),
                        'supportEmail' => config('mail.support_email', 'support@pugos.cn'),
                        'companyName' => config('app.name', 'PuGOS')
                    ]);
        
        // Attach invoice file if it exists and type is 'sent'
        if ($this->type === 'sent' && $this->invoice->file_path && Storage::exists($this->invoice->file_path)) {
            $mail->attachFromStorage($this->invoice->file_path, $this->invoice->invoice_number . '.pdf', [
                'mime' => 'application/pdf'
            ]);
        }
        
        return $mail;
    }
}
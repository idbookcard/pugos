<?php
// app/Services/NotificationService.php
namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OrderConfirmation;
use App\Mail\OrderStatusUpdate;
use App\Mail\PaymentConfirmation;
use App\Mail\InvoiceMail;

class NotificationService
{
    /**
     * Send order confirmation email
     *
     * @param Order $order
     * @return bool
     */
    public function sendOrderConfirmation(Order $order)
    {
        try {
            $user = $order->user;
            
            if (!$user || !$user->email) {
                Log::warning('Unable to send order confirmation: invalid user or email', [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id
                ]);
                return false;
            }
            
            Mail::to($user->email)->send(new OrderConfirmation($order));
            
            Log::info('Order confirmation email sent', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Send order status update email
     *
     * @param Order $order
     * @param string $oldStatus
     * @return bool
     */
    public function sendOrderStatusUpdate(Order $order, $oldStatus)
    {
        try {
            $user = $order->user;
            
            if (!$user || !$user->email) {
                Log::warning('Unable to send status update: invalid user or email', [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id
                ]);
                return false;
            }
            
            // Only send email for significant status changes
            $significantChanges = [
                'pending' => ['processing', 'completed', 'canceled', 'rejected'],
                'processing' => ['completed', 'canceled', 'rejected'],
            ];
            
            if (isset($significantChanges[$oldStatus]) && in_array($order->status, $significantChanges[$oldStatus])) {
                Mail::to($user->email)->send(new OrderStatusUpdate($order, $oldStatus));
                
                Log::info('Order status update email sent', [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'old_status' => $oldStatus,
                    'new_status' => $order->status
                ]);
                
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to send order status update email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Send payment confirmation email
     *
     * @param Transaction $transaction
     * @return bool
     */
    public function sendPaymentConfirmation(Transaction $transaction)
    {
        try {
            $user = $transaction->user;
            
            if (!$user || !$user->email) {
                Log::warning('Unable to send payment confirmation: invalid user or email', [
                    'transaction_id' => $transaction->id,
                    'user_id' => $transaction->user_id
                ]);
                return false;
            }
            
            Mail::to($user->email)->send(new PaymentConfirmation($transaction));
            
            Log::info('Payment confirmation email sent', [
                'transaction_id' => $transaction->id,
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Send invoice status email
     *
     * @param Invoice $invoice
     * @param string $type (approved, rejected, sent)
     * @return bool
     */
    public function sendInvoiceEmail(Invoice $invoice, $type)
    {
        try {
            $email = $invoice->email ?? $invoice->user->email;
            
            if (!$email) {
                Log::warning('Unable to send invoice email: invalid email', [
                    'invoice_id' => $invoice->id,
                    'user_id' => $invoice->user_id
                ]);
                return false;
            }
            
            Mail::to($email)->send(new InvoiceMail($invoice, $type));
            
            Log::info('Invoice email sent', [
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id,
                'email' => $email,
                'type' => $type
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
}
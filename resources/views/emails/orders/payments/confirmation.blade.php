<!-- resources/views/emails/payments/confirmation.blade.php -->
@component('mail::message')
# Payment Confirmation

Dear {{ $user->name }},

We're confirming that we've received your payment.

**Reference ID:** {{ $transaction->reference_id }}
**Date:** {{ $transaction->created_at->format('Y-m-d H:i') }}
**Amount:** {{ number_format($transaction->amount, 2) }}
**Payment Method:** {{ $paymentMethod }}

@if($transaction->transaction_type == 'deposit')
Your account balance has been updated to reflect this payment.
@elseif($transaction->transaction_type == 'order_payment')
This payment is for your order: #{{ $transaction->order->order_number ?? 'N/A' }}
@endif

@if(count($paymentDetails) > 0 && $transaction->status == 'pending')
## Payment Details
@foreach($paymentDetails as $label => $value)
**{{ $label }}:** {{ $value }}
@endforeach

Please complete the payment using the details above.
@endif

@component('mail::button', ['url' => $walletUrl])
View Wallet
@endcomponent

If you have any questions about this payment, please contact our support team.

Thanks,<br>
{{ $companyName }} Team

@component('mail::subcopy')
If you're having trouble clicking the "View Wallet" button, copy and paste the URL below into your web browser: {{ $walletUrl }}
@endcomponent
@endcomponent
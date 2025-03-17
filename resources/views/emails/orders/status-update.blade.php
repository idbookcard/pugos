<!-- resources/views/emails/orders/status-update.blade.php -->
@component('mail::message')
# Order Status Update

Dear {{ $user->name }},

{{ $statusMessage }}

**Order Number:** {{ $order->order_number }}
**Package:** {{ $package->name }}
**Current Status:** {{ ucfirst($order->status) }}
**Previous Status:** {{ ucfirst($oldStatus) }}
**Updated At:** {{ $order->updated_at->format('Y-m-d H:i') }}

@if($order->status == 'completed')
## Order Completion Details
Your SEO backlink order has been successfully completed! You should start seeing the effects in search engine rankings in the coming weeks.

@if($order->completed_at)
**Completion Date:** {{ $order->completed_at->format('Y-m-d H:i') }}
@endif
@endif

@if($order->status == 'rejected')
## Rejection Information
If you have any questions about why your order was rejected, please contact our support team for clarification.
@endif

@component('mail::button', ['url' => $orderUrl])
View Order Details
@endcomponent

If you have any questions, please feel free to contact our support team.

Thanks,<br>
{{ $companyName }} Team

@component('mail::subcopy')
If you're having trouble clicking the "View Order Details" button, copy and paste the URL below into your web browser: {{ $orderUrl }}
@endcomponent
@endcomponent
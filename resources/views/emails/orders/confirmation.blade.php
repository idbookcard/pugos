<!-- resources/views/emails/orders/confirmation.blade.php -->

@component('mail::message')
# Order Confirmation

Dear {{ $user->name }},

Thank you for your order! We've received your order and it is now being processed.

**Order Number:** {{ $order->order_number }}
**Date:** {{ $order->created_at->format('Y-m-d H:i') }}
**Package:** {{ $package->name }}
**Total Amount:** {{ number_format($order->total_amount, 2) }}

**Target URL:** {{ $order->target_url }}
**Keywords:** {{ $order->keywords }}

@if($order->extra_data && isset($order->extra_data['notes']))
**Additional Notes:** {{ $order->extra_data['notes'] }}
@endif

@component('mail::button', ['url' => $orderUrl])
View Order Details
@endcomponent

If you have any questions about your order, please feel free to contact our support team.

Thanks,<br>
{{ $companyName }} Team

@component('mail::subcopy')
If you're having trouble clicking the "View Order Details" button, copy and paste the URL below into your web browser: {{ $orderUrl }}
@endcomponent
@endcomponent
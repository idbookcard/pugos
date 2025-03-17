<!-- resources/views/emails/invoices/status.blade.php -->
@component('mail::message')
# Invoice {{ ucfirst($type) }}

Dear {{ $user->name }},

{{ $statusMessage }}

**Invoice Number:** {{ $invoice->invoice_number }}
**Date:** {{ $invoice->created_at->format('Y-m-d H:i') }}
**Amount:** {{ number_format($invoice->amount, 2) }}
**Type:** {{ $invoice->invoice_type == 'regular' ? 'Regular Invoice' : 'VAT Invoice' }}

@if($type == 'rejected' && $invoice->rejection_reason)
## Rejection Reason
{{ $invoice->rejection_reason }}

You can submit a new invoice request with the corrected information.
@endif

@if($type == 'sent')
## Invoice
Your invoice has been attached to this email. You can also download it from your account.
@endif

@component('mail::button', ['url' => $invoiceUrl])
View Invoice Details
@endcomponent

If you have any questions about your invoice, please contact our support team.

Thanks,<br>
{{ $companyName }} Team

@component('mail::subcopy')
If you're having trouble clicking the "View Invoice Details" button, copy and paste the URL below into your web browser: {{ $invoiceUrl }}
@endcomponent
@endcomponent
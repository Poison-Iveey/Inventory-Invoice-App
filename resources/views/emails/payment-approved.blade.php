<div style="font-family: sans-serif; color: #111827;">
    <p>Dear {{ $invoice->customer->name }},</p>
    <p>We've confirmed your payment for invoice <strong>{{ $invoice->invoice_number }}</strong> from {{ config('company.name') }}. This invoice is now marked as <strong>paid</strong>.</p>
    <table style="border-collapse: collapse; margin: 16px 0;">
        <tr>
            <td style="padding: 4px 12px 4px 0; color: #6b7280;">Invoice</td>
            <td style="padding: 4px 0;">{{ $invoice->invoice_number }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0; color: #6b7280;">Amount paid</td>
            <td style="padding: 4px 0; font-weight: bold;">KSh {{ number_format($invoice->total, 2) }}</td>
        </tr>
    </table>
    <p>The updated PDF copy of this invoice is attached to this email.</p>
    <p>Thank you for your business.<br>{{ config('company.name') }}</p>
</div>

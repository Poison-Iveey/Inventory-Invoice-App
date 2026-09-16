<div style="font-family: sans-serif; color: #111827;">
    <p>Dear {{ $invoice->customer->name }},</p>
    <p>Please find attached your invoice <strong>{{ $invoice->invoice_number }}</strong> from {{ config('company.name') }}.</p>
    <table style="border-collapse: collapse; margin: 16px 0;">
        <tr>
            <td style="padding: 4px 12px 4px 0; color: #6b7280;">Issue date</td>
            <td style="padding: 4px 0;">{{ $invoice->issue_date }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0; color: #6b7280;">Due date</td>
            <td style="padding: 4px 0;">{{ $invoice->due_date }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0; color: #6b7280;">Amount due</td>
            <td style="padding: 4px 0; font-weight: bold;">KSh {{ number_format($invoice->total, 2) }}</td>
        </tr>
    </table>
    <p>The PDF copy of this invoice is attached to this email.</p>
    <p>Thank you for your business.<br>{{ config('company.name') }}</p>
</div>
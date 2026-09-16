<?php

namespace App\Jobs;

use App\Mail\PaymentApprovedMail;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

// Deliberately NOT a queued job — see SendInvoiceEmail for why.
class SendPaymentApprovedEmail
{
    use Dispatchable, Queueable, SerializesModels;

    public Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function handle(): void
    {
        $invoice = $this->invoice->load(['customer', 'items.product']);

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice])->output();

        if ($invoice->customer && $invoice->customer->email) {
            Mail::to($invoice->customer->email)->send(new PaymentApprovedMail($invoice, $pdf));
        }
    }
}

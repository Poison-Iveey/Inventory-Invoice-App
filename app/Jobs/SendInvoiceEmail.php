<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInvoiceEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
            Mail::to($invoice->customer->email)->send(new InvoiceMail($invoice, $pdf));
        }
    }
}

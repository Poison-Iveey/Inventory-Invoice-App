<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

// Deliberately NOT a queued job (no ShouldQueue): this project's dev workflow
// is just `php artisan serve` with no separate queue worker running, so a
// queued job here would silently sit in the `jobs` table forever — exactly
// the bug this was previously stuck on. Sends immediately instead, matching
// the AccountCreated notification's synchronous pattern.
class SendInvoiceEmail
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
            Mail::to($invoice->customer->email)->send(new InvoiceMail($invoice, $pdf));
        }
    }
}

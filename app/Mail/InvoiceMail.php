<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    public $pdfData;

    public function __construct(Invoice $invoice, string $pdfData)
    {
        $this->invoice = $invoice;
        $this->pdfData = $pdfData;
    }

    public function build()
    {
        $m = $this->subject('Invoice '.$this->invoice->invoice_number)
            ->view('emails.invoice')
            ->with(['invoice' => $this->invoice]);

        if ($this->pdfData) {
            $m->attachData($this->pdfData, 'invoice-'.$this->invoice->invoice_number.'.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        return $m;
    }
}

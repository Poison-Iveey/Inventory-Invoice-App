<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentApprovedMail extends Mailable
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
        $m = $this->subject('Payment confirmed for invoice '.$this->invoice->invoice_number)
            ->view('emails.payment-approved')
            ->with(['invoice' => $this->invoice]);

        if ($this->pdfData) {
            $m->attachData($this->pdfData, 'invoice-'.$this->invoice->invoice_number.'.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        return $m;
    }
}

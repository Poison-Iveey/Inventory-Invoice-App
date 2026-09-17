<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Notifications\Notification;

/**
 * In-app bell notification for the customer's own linked login, mirroring
 * (approval) or filling a gap left by (rejection has no email at all) the
 * existing PaymentApprovedMail. Not queued — see PaymentSubmitted for why.
 */
class PaymentReviewed extends Notification
{
    public function __construct(private Payment $payment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $invoice = $this->payment->invoice;
        $approved = $this->payment->status === 'approved';

        return [
            'title' => $approved ? 'Payment approved' : 'Payment rejected',
            'body' => $approved
                ? sprintf(
                    'Your payment of KSh %s for %s was approved. The invoice is now paid.',
                    number_format($this->payment->amount, 2),
                    $invoice->invoice_number,
                )
                : sprintf(
                    'Your payment for %s was rejected: %s',
                    $invoice->invoice_number,
                    $this->payment->rejection_reason,
                ),
            'url' => route('invoices.show', $invoice),
        ];
    }
}

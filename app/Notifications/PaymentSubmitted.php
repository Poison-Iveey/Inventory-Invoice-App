<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Notifications\Notification;

/**
 * In-app bell notification only (no email) — tells every Accountant a
 * customer's self-submitted payment is waiting for review. Deliberately not
 * a queued notification: this project's dev workflow has no queue worker
 * running, so anything queued here would silently never appear.
 */
class PaymentSubmitted extends Notification
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

        return [
            'title' => 'Payment submitted for review',
            'body' => sprintf(
                '%s submitted a %s payment of KSh %s for %s.',
                $invoice->customer->name,
                ucfirst($this->payment->method),
                number_format($this->payment->amount, 2),
                $invoice->invoice_number,
            ),
            'url' => route('invoices.show', $invoice),
        ];
    }
}

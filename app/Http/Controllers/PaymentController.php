<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Jobs\SendPaymentApprovedEmail;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Payment::class);

        $user = $request->user();
        $status = $request->get('status');

        $query = Payment::with(['invoice.customer'])->orderByDesc('id');

        if ($user->isCustomer()) {
            $query->whereHas('invoice.customer', fn ($q) => $q->where('user_id', $user->id));
        }

        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $payments = $query->paginate(15)->withQueryString();

        return Inertia::render('Payments/Index', [
            'payments' => PaymentResource::collection($payments),
            'filters' => ['status' => $status],
        ]);
    }

    /**
     * Either a customer paying their own invoice online (status starts
     * pending, awaits accountant review) or an accountant recording a
     * payment the customer made outside the system (self-approved on entry,
     * since the accountant is the one vouching for it).
     */
    public function store(StorePaymentRequest $request, Invoice $invoice)
    {
        $this->authorize('create', Payment::class);
        $user = $request->user();

        if ($user->isCustomer() && $invoice->customer?->user_id !== $user->id) {
            abort(403);
        }

        if (! in_array($invoice->status, ['sent', 'overdue'], true)) {
            throw ValidationException::withMessages([
                'reference' => ['This invoice is not awaiting payment.'],
            ]);
        }

        if ($invoice->payments()->where('status', 'pending')->exists()) {
            throw ValidationException::withMessages([
                'reference' => ['A payment for this invoice is already awaiting approval.'],
            ]);
        }

        $attributes = [
            ...$request->validated(),
            'amount' => $invoice->total,
            'status' => 'pending',
        ];

        if ($user->isAccountant()) {
            $attributes['status'] = 'approved';
            $attributes['reviewed_by'] = $user->id;
            $attributes['reviewed_at'] = now();
        }

        $invoice->payments()->create($attributes);

        if ($user->isAccountant()) {
            $invoice->update(['status' => 'paid']);
            SendPaymentApprovedEmail::dispatch($invoice);

            return back()->with('success', 'Payment recorded and invoice marked as paid.');
        }

        return back()->with('success', 'Payment submitted. The accountant will review it shortly.');
    }

    public function approve(Payment $payment)
    {
        $this->authorize('review', $payment);

        $payment->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $payment->invoice->update(['status' => 'paid']);

        // dispatched to the default queue (see SendInvoiceEmail for why this matters)
        SendPaymentApprovedEmail::dispatch($payment->invoice);

        return back()->with('success', 'Payment approved and invoice marked as paid.');
    }

    public function reject(Request $request, Payment $payment)
    {
        $this->authorize('review', $payment);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $payment->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        return back()->with('success', 'Payment rejected.');
    }
}

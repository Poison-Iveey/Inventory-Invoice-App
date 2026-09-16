<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    private const PER_TYPE_LIMIT = 5;

    /**
     * Global header search: a handful of matches per resource type, scoped
     * by the same policy/ownership rules as each resource's own index page
     * (e.g. a customer only ever sees their own invoices/payments here too).
     */
    public function index(Request $request): JsonResponse
    {
        $query = trim((string) $request->string('q'));
        $user = $request->user();

        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = collect();

        if ($user->can('viewAny', Product::class)) {
            $results = $results->merge($this->searchProducts($query, $user));
        }

        if ($user->can('viewAny', Customer::class)) {
            $results = $results->merge($this->searchCustomers($query));
        }

        if ($user->can('viewAny', Invoice::class)) {
            $results = $results->merge($this->searchInvoices($query, $user));
        }

        if ($user->can('viewAny', Payment::class)) {
            $results = $results->merge($this->searchPayments($query, $user));
        }

        if ($user->can('viewAny', User::class)) {
            $results = $results->merge($this->searchUsers($query));
        }

        return response()->json(['results' => $results->values()]);
    }

    private function searchProducts(string $query, User $user): array
    {
        $products = Product::query()
            ->when($user->isCustomer(), fn ($q) => $q->where('status', 'active'))
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")->orWhere('sku', 'like', "%{$query}%");
            })
            ->limit(self::PER_TYPE_LIMIT)
            ->get();

        return $products->map(fn ($product) => [
            'type' => 'product',
            'label' => $product->name,
            'sub' => $user->isCustomer()
                ? 'KSh '.number_format($product->price, 2)
                : $product->sku.' · KSh '.number_format($product->price, 2),
            // customers only ever get the read-only catalog list, never a per-product page
            'url' => $user->isCustomer()
                ? route('products.index', ['search' => $product->name])
                : route('products.show', $product),
        ])->all();
    }

    private function searchCustomers(string $query): array
    {
        $customers = Customer::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(self::PER_TYPE_LIMIT)
            ->get();

        return $customers->map(fn ($customer) => [
            'type' => 'customer',
            'label' => $customer->name,
            'sub' => $customer->email,
            'url' => route('customers.show', $customer),
        ])->all();
    }

    private function searchInvoices(string $query, User $user): array
    {
        $invoices = Invoice::query()
            ->with('customer:id,name')
            ->when(
                $user->isCustomer(),
                fn ($q) => $q->whereHas('customer', fn ($c) => $c->where('user_id', $user->id))
            )
            ->where(function ($q) use ($query) {
                $q->where('invoice_number', 'like', "%{$query}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$query}%"));
            })
            ->limit(self::PER_TYPE_LIMIT)
            ->get();

        return $invoices->map(fn ($invoice) => [
            'type' => 'invoice',
            'label' => $invoice->invoice_number,
            'sub' => ($invoice->customer->name ?? 'Unknown customer').' · '.ucfirst($invoice->status),
            'url' => route('invoices.show', $invoice),
        ])->all();
    }

    private function searchPayments(string $query, User $user): array
    {
        $payments = Payment::query()
            ->with('invoice.customer:id,name')
            ->when(
                $user->isCustomer(),
                fn ($q) => $q->whereHas('invoice.customer', fn ($c) => $c->where('user_id', $user->id))
            )
            ->where('reference', 'like', "%{$query}%")
            ->limit(self::PER_TYPE_LIMIT)
            ->get();

        return $payments->map(fn ($payment) => [
            'type' => 'payment',
            'label' => $payment->reference,
            'sub' => ($payment->invoice->customer->name ?? 'Unknown customer').' · '.ucfirst($payment->status),
            'url' => route('invoices.show', $payment->invoice_id),
        ])->all();
    }

    private function searchUsers(string $query): array
    {
        $users = User::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(self::PER_TYPE_LIMIT)
            ->get();

        return $users->map(fn ($user) => [
            'type' => 'user',
            'label' => $user->name,
            'sub' => $user->email.' · '.ucfirst($user->role),
            'url' => route('users.edit', $user),
        ])->all();
    }
}

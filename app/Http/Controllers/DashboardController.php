<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Http\Resources\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $invoices = Invoice::query();

        if ($user->isCustomer()) {
            $invoices->whereHas('customer', fn ($query) => $query->where('user_id', $user->id));
        }

        $invoiceQuery = clone $invoices;
        $canViewFinancialMetrics = ! $user->isStaff();

        return Inertia::render('Dashboard', [
            'metrics' => $canViewFinancialMetrics ? [
                'invoice_count' => $invoiceQuery->count(),
                'paid_revenue' => (float) (clone $invoices)->where('status', 'paid')->sum('total'),
                'outstanding_total' => (float) (clone $invoices)->whereIn('status', ['sent', 'overdue'])->sum('total'),
                'overdue_count' => (clone $invoices)->where('status', 'overdue')->count(),
                'inventory_units' => $user->isAdmin() ? Product::sum('stock') : null,
            ] : null,
            'statusBreakdown' => $canViewFinancialMetrics ? $this->statusBreakdown(clone $invoices) : null,
            'weeklySales' => $canViewFinancialMetrics ? $this->weeklySales(clone $invoices) : null,
            'recentInvoices' => InvoiceResource::collection(
                $invoices->with('customer')->orderByDesc('id')->limit(5)->get()
            ),
            'recentPayments' => $user->isCustomer() ? PaymentResource::collection(
                Payment::with(['invoice.customer'])
                    ->whereHas('invoice.customer', fn ($query) => $query->where('user_id', $user->id))
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get()
            ) : null,
        ]);
    }

    /**
     * Invoice counts per status, for the dashboard's status breakdown chart.
     */
    private function statusBreakdown($invoices): array
    {
        $counts = (clone $invoices)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'draft' => (int) ($counts['draft'] ?? 0),
            'sent' => (int) ($counts['sent'] ?? 0),
            'paid' => (int) ($counts['paid'] ?? 0),
            'overdue' => (int) ($counts['overdue'] ?? 0),
        ];
    }

    /**
     * Invoice totals per day for the last 7 days, for the sales-over-time chart.
     */
    private function weeklySales($invoices): array
    {
        $today = Carbon::today();
        $startDate = $today->copy()->subDays(6);

        $dailyTotals = (clone $invoices)
            ->whereBetween('issue_date', [$startDate->toDateString(), $today->toDateString()])
            ->selectRaw('issue_date, sum(total) as total')
            ->groupBy('issue_date')
            ->pluck('total', 'issue_date');

        return collect(range(0, 6))->map(function (int $offset) use ($startDate, $dailyTotals) {
            $date = $startDate->copy()->addDays($offset);

            return [
                'label' => $date->format('D'),
                'total' => (float) ($dailyTotals[$date->toDateString()] ?? 0),
            ];
        })->all();
    }
}

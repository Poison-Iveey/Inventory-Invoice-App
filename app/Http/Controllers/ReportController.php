<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('view-reports');

        [$from, $to, $invoices, $statusCounts, $topProducts] = $this->buildReport($request);

        return Inertia::render('Reports/Index', [
            'filters' => ['from' => $from, 'to' => $to],
            'summary' => [
                'invoice_count' => (clone $invoices)->count(),
                'paid_revenue' => (float) (clone $invoices)->where('status', 'paid')->sum('total'),
                'outstanding_total' => (float) (clone $invoices)->whereIn('status', ['sent', 'overdue'])->sum('total'),
                'overdue_count' => (int) ($statusCounts['overdue'] ?? 0),
                'status_counts' => [
                    'draft' => (int) ($statusCounts['draft'] ?? 0),
                    'sent' => (int) ($statusCounts['sent'] ?? 0),
                    'paid' => (int) ($statusCounts['paid'] ?? 0),
                    'overdue' => (int) ($statusCounts['overdue'] ?? 0),
                ],
            ],
            'topProducts' => $topProducts,
        ]);
    }

    public function export(Request $request)
    {
        Gate::authorize('export-reports');

        [$from, $to, $invoices, , $topProducts] = $this->buildReport($request);

        $rows = [
            ['Report period', $from.' to '.$to],
            [],
            ['Metric', 'Value'],
            ['Invoices', (clone $invoices)->count()],
            ['Paid revenue', (clone $invoices)->where('status', 'paid')->sum('total')],
            ['Outstanding', (clone $invoices)->whereIn('status', ['sent', 'overdue'])->sum('total')],
            [],
            ['Top paid products', 'Quantity sold', 'Revenue'],
            ...$topProducts->map(fn ($product) => [$product['name'], $product['quantity_sold'], $product['revenue']])->all(),
        ];

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'report-'.$from.'-to-'.$to.'.csv', ['Content-Type' => 'text/csv']);
    }

    private function buildReport(Request $request): array
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? now()->toDateString();

        $invoices = Invoice::query()->whereBetween('issue_date', [$from, $to]);
        $statusCounts = (clone $invoices)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $topProducts = InvoiceItem::query()
            ->selectRaw('products.name, sum(invoice_items.quantity) as quantity_sold, sum(invoice_items.total) as revenue')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->whereBetween('invoices.issue_date', [$from, $to])
            ->where('invoices.status', 'paid')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('quantity_sold')
            ->limit(5)
            ->get()
            ->map(fn ($product) => [
                'name' => $product->name,
                'quantity_sold' => (int) $product->quantity_sold,
                'revenue' => (float) $product->revenue,
            ]);

        return [$from, $to, $invoices, $statusCounts, $topProducts];
    }
}

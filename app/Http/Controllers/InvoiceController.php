<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Jobs\SendInvoiceEmail;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::with('customer')->orderBy('id', 'desc')->paginate(15);

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Invoice::class);

        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return Inertia::render('Invoices/Create', [
            'customers' => $customers,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request)
    {
        $data = $request->validated();

        try {
            $invoice = DB::transaction(function () use ($data) {
                $subtotal = 0;
                $items = $data['items'];

                // Validate items (existence and stock) while holding row locks
                foreach ($items as $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);

                    if (! $product) {
                        throw ValidationException::withMessages([
                            'items' => ['One of the selected products could not be found.'],
                        ]);
                    }

                    if ($product->stock < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => [
                                'Insufficient stock for "'.$product->name.'". Available: '.$product->stock.'. Requested: '.$item['quantity'].'.',
                            ],
                        ]);
                    }
                }

                $invoiceNumber = 'INV-'.date('Ymd').'-'.str_pad((string) (Invoice::count() + 1), 4, '0', STR_PAD_LEFT);

                $invoice = Invoice::create([
                    'customer_id' => $data['customer_id'],
                    'invoice_number' => $invoiceNumber,
                    'issue_date' => $data['issue_date'],
                    'due_date' => $data['due_date'],
                    'status' => 'sent',
                    'subtotal' => 0,
                    'tax' => 0,
                    'total' => 0,
                ]);

                $subtotal = 0;
                foreach ($items as $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);
                    $lineTotal = $product->price * $item['quantity'];

                    $invoice->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->price,
                        'total' => $lineTotal,
                    ]);

                    $product->decrement('stock', $item['quantity']);
                    $subtotal += $lineTotal;
                }

                // update invoice totals
                $invoice->update([
                    'subtotal' => $subtotal,
                    'total' => $subtotal + $invoice->tax,
                ]);

                return $invoice;
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'items' => [$e->getMessage()],
            ]);
        }

        // dispatch email job after transaction completes
        SendInvoiceEmail::dispatch($invoice)->onQueue('emails');

        return redirect()->route('invoices.show', $invoice->id)->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $invoice = Invoice::with(['customer', 'items.product'])->findOrFail($id);

        $this->authorize('view', $invoice);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Download the invoice PDF.
     */
    public function pdf(string $id)
    {
        $invoice = Invoice::with(['customer', 'items.product'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
        ]);

        return $pdf->download('invoice-'.$invoice->invoice_number.'.pdf');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // not required for this iteration
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // not required for this iteration
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // not required for this iteration
    }
}

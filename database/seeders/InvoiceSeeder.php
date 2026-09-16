<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    private int $invoiceCount = 0;

    /**
     * Creates a handful of invoices covering every status (draft, sent,
     * paid, overdue) so the dashboard, invoice list and reports page all
     * have something to show.
     */
    public function run(): void
    {
        $alpha = Customer::where('email', 'alpha@demo.test')->first();
        $beta = Customer::where('email', 'beta@demo.test')->first();
        $greenValley = Customer::where('email', 'orders@greenvalleystore.test')->first();
        $metro = Customer::where('email', 'accounts@metrotraders.test')->first();

        // paid invoice, already settled
        $this->createInvoice($alpha, 'paid', now()->subDays(15), now()->subDays(8), [
            ['sku' => 'SKU-WM-1001', 'quantity' => 2],
            ['sku' => 'SKU-KB-1002', 'quantity' => 1],
        ]);

        // sent invoice, not yet due
        $this->createInvoice($beta, 'sent', now()->subDays(3), now()->addDays(4), [
            ['sku' => 'SKU-HUB-1003', 'quantity' => 3],
        ]);

        // draft invoice, still being put together
        $this->createInvoice($greenValley, 'draft', now(), now()->addDays(14), [
            ['sku' => 'SKU-MON-1005', 'quantity' => 1],
            ['sku' => 'SKU-CAM-1006', 'quantity' => 2],
        ]);

        // overdue invoice, past its due date and never paid
        $this->createInvoice($metro, 'overdue', now()->subDays(25), now()->subDays(10), [
            ['sku' => 'SKU-LMP-1008', 'quantity' => 5],
        ]);

        // sent invoice whose due date has already passed - visiting the
        // invoice list flips this one to "overdue" automatically
        $this->createInvoice($alpha, 'sent', now()->subDays(20), now()->subDays(2), [
            ['sku' => 'SKU-CH-1007', 'quantity' => 1],
        ]);
    }

    private function createInvoice(Customer $customer, string $status, $issueDate, $dueDate, array $lines): void
    {
        $this->invoiceCount++;

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-DEMO-'.str_pad((string) $this->invoiceCount, 4, '0', STR_PAD_LEFT),
            'issue_date' => $issueDate->toDateString(),
            'due_date' => $dueDate->toDateString(),
            'status' => $status,
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
        ]);

        $subtotal = 0;

        foreach ($lines as $line) {
            $product = Product::where('sku', $line['sku'])->first();
            $lineTotal = $product->price * $line['quantity'];

            $invoice->items()->create([
                'product_id' => $product->id,
                'quantity' => $line['quantity'],
                'unit_price' => $product->price,
                'total' => $lineTotal,
            ]);

            $product->decrement('stock', $line['quantity']);
            $subtotal += $lineTotal;
        }

        $invoice->update([
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);
    }
}

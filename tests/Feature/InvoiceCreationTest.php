<?php

namespace Tests\Feature;

use App\Jobs\SendInvoiceEmail;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class InvoiceCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_creation_deducts_stock_and_creates_a_draft()
    {
        Bus::fake();

        $user = User::factory()->create(['role' => 'staff']);
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 100]);

        $payload = [
            'customer_id' => $customer->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('invoices.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'customer_id' => $customer->id,
            'status' => 'draft',
            'subtotal' => 300,
            'total' => 300,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 7,
        ]);

        Bus::assertNotDispatched(SendInvoiceEmail::class);
    }

    public function test_invoice_creation_fails_with_insufficient_stock()
    {
        $user = User::factory()->create(['role' => 'staff']);
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 1, 'price' => 50]);

        $payload = [
            'customer_id' => $customer->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('invoices.store'), $payload);

        $response->assertSessionHasErrors('items');

        // ensure stock unchanged
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 1,
        ]);
    }
}

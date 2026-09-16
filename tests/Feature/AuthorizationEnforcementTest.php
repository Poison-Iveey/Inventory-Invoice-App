<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_browse_the_read_only_product_catalog_but_not_the_full_detail_or_other_customers(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create();
        $customerRecord = Customer::factory()->create();

        // read-only catalog is allowed
        $this->actingAs($customer)
            ->get(route('products.index'))
            ->assertOk();

        // the full admin-style product detail page (SKU, exact stock) is not
        $this->actingAs($customer)
            ->get(route('products.show', $product))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('customers.index'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('customers.show', $customerRecord))
            ->assertForbidden();
    }

    public function test_customer_only_receives_invoices_for_its_linked_customer_profile(): void
    {
        $customerUser = User::factory()->create(['role' => 'customer']);
        $ownCustomer = Customer::factory()->create(['user_id' => $customerUser->id]);
        $otherCustomer = Customer::factory()->create();
        $ownInvoice = Invoice::factory()->create(['customer_id' => $ownCustomer->id]);
        $otherInvoice = Invoice::factory()->create(['customer_id' => $otherCustomer->id]);

        $this->actingAs($customerUser)
            ->get(route('invoices.index'))
            ->assertOk()
            ->assertSee($ownInvoice->invoice_number)
            ->assertDontSee($otherInvoice->invoice_number);

        $this->actingAs($customerUser)
            ->get(route('invoices.pdf', $otherInvoice))
            ->assertForbidden();
    }

    public function test_customer_cannot_create_an_invoice_by_posting_to_the_endpoint_directly(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $customerRecord = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($customer)
            ->post(route('invoices.store'), [
                'customer_id' => $customerRecord->id,
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addWeek()->toDateString(),
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ])
            ->assertForbidden();
    }
}

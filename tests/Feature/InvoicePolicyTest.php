<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_invoices()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        $this->actingAs($user)
            ->post(route('invoices.store'), [
                'customer_id' => $customer->id,
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2],
                ],
            ])
            ->assertRedirect();
    }

    public function test_staff_can_create_invoices()
    {
        $user = User::factory()->create(['role' => 'staff']);
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        $this->actingAs($user)
            ->post(route('invoices.store'), [
                'customer_id' => $customer->id,
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
            ])
            ->assertRedirect();
    }

    public function test_accountant_cannot_create_invoices()
    {
        $user = User::factory()->create(['role' => 'accountant']);
        
        $this->actingAs($user)
            ->get(route('invoices.create'))
            ->assertStatus(403);
    }

    public function test_customer_cannot_create_invoices()
    {
        $user = User::factory()->create(['role' => 'customer']);
        
        $this->actingAs($user)
            ->get(route('invoices.create'))
            ->assertStatus(403);
    }

    public function test_admin_can_view_all_invoices()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $invoice = Invoice::factory()->create();
        
        $this->actingAs($admin)
            ->get(route('invoices.show', $invoice))
            ->assertStatus(200);
    }

    public function test_staff_can_view_all_invoices()
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $invoice = Invoice::factory()->create();
        
        $this->actingAs($staff)
            ->get(route('invoices.show', $invoice))
            ->assertStatus(200);
    }

    public function test_accountant_can_view_all_invoices()
    {
        $accountant = User::factory()->create(['role' => 'accountant']);
        $invoice = Invoice::factory()->create();
        
        $this->actingAs($accountant)
            ->get(route('invoices.show', $invoice))
            ->assertStatus(200);
    }

    public function test_customer_can_only_view_own_invoices()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $customer_model = Customer::factory()->create();
        $other_customer = Customer::factory()->create();

        // Create invoice for customer
        $own_invoice = Invoice::factory()->create(['customer_id' => $customer_model->id]);
        // Create invoice for another customer
        $other_invoice = Invoice::factory()->create(['customer_id' => $other_customer->id]);
        
        // Can view own invoice
        $this->actingAs($customer)
            ->get(route('invoices.show', $own_invoice))
            ->assertStatus(200);
        
        // Cannot view other's invoice
        $this->actingAs($customer)
            ->get(route('invoices.show', $other_invoice))
            ->assertStatus(403);
    }
}

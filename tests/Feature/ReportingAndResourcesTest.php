<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportingAndResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_list_filters_and_uses_a_deliberate_resource_shape(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::factory()->create(['name' => 'Acme Supplies']);
        $match = Invoice::factory()->create(['customer_id' => $customer->id, 'invoice_number' => 'INV-MATCH', 'status' => 'paid']);
        Invoice::factory()->create(['invoice_number' => 'INV-OTHER', 'status' => 'draft']);

        $this->actingAs($admin)
            ->get(route('invoices.index', ['search' => 'Acme', 'status' => 'paid']))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Invoices/Index')
                ->has('invoices.data', 1)
                ->where('invoices.data.0.id', $match->id)
                ->where('invoices.data.0.invoice_number', 'INV-MATCH')
                ->missing('invoices.data.0.customer_id')
                ->has('invoices.meta.current_page'));
    }

    public function test_product_list_uses_product_resource_and_status_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $active = Product::factory()->create(['status' => 'active']);
        Product::factory()->create(['status' => 'inactive']);

        $this->actingAs($admin)
            ->get(route('products.index', ['status' => 'active']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('products.data', 1)
                ->where('products.data.0.id', $active->id)
                ->has('products.data.0.image_url')
                ->missing('products.data.0.image'));
    }

    public function test_reports_are_limited_to_administrators_and_accountants(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('reports.index'))
            ->assertForbidden();
    }

    public function test_report_metrics_include_paid_revenue_and_overdue_count(): void
    {
        $accountant = User::factory()->create(['role' => 'accountant']);
        Invoice::factory()->create(['status' => 'paid', 'total' => 150, 'issue_date' => now()->toDateString()]);
        Invoice::factory()->create(['status' => 'overdue', 'total' => 70, 'issue_date' => now()->toDateString()]);

        $this->actingAs($accountant)
            ->get(route('reports.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/Index')
                ->where('summary.paid_revenue', 150)
                ->where('summary.overdue_count', 1));
    }

    public function test_customer_dashboard_only_contains_its_own_invoices(): void
    {
        $customerUser = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);
        $ownInvoice = Invoice::factory()->create(['customer_id' => $customer->id]);
        Invoice::factory()->create();

        $this->actingAs($customerUser)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('metrics.invoice_count', 1)
                ->has('recentInvoices.data', 1)
                ->where('recentInvoices.data.0.id', $ownInvoice->id));
    }

    public function test_staff_dashboard_does_not_expose_financial_metrics(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('metrics', null));
    }
}

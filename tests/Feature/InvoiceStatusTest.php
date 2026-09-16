<?php

namespace Tests\Feature;

use App\Jobs\SendInvoiceEmail;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class InvoiceStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_send_a_draft_invoice_and_queue_its_email(): void
    {
        Bus::fake();
        $staff = User::factory()->create(['role' => 'staff']);
        $invoice = Invoice::factory()->create(['status' => 'draft']);

        $this->actingAs($staff)
            ->post(route('invoices.send', $invoice))
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'sent']);
        Bus::assertDispatched(SendInvoiceEmail::class, fn ($job) => $job->invoice->is($invoice));
    }

    public function test_admin_cannot_send_a_draft_invoice(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $invoice = Invoice::factory()->create(['status' => 'draft']);

        $this->actingAs($admin)
            ->post(route('invoices.send', $invoice))
            ->assertForbidden();
    }

    public function test_accountant_can_record_a_payment_to_mark_an_overdue_invoice_paid(): void
    {
        $accountant = User::factory()->create(['role' => 'accountant']);
        $invoice = Invoice::factory()->create(['status' => 'overdue', 'due_date' => now()->subDay()]);

        $this->actingAs($accountant)
            ->post(route('payments.store', $invoice), [
                'method' => 'card',
                'reference' => 'Receipt #4521',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid']);
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'status' => 'approved',
            'reference' => 'Receipt #4521',
        ]);
    }

    public function test_sent_past_due_invoices_become_overdue_when_the_list_is_viewed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $invoice = Invoice::factory()->create(['status' => 'sent', 'due_date' => now()->subDay()]);

        $this->actingAs($admin)
            ->get(route('invoices.index'))
            ->assertOk();

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'overdue']);
    }

    public function test_customer_cannot_send_an_invoice(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $draft = Invoice::factory()->create(['status' => 'draft']);

        $this->actingAs($customer)
            ->post(route('invoices.send', $draft))
            ->assertForbidden();
    }
}

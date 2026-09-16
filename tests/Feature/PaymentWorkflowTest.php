<?php

namespace Tests\Feature;

use App\Jobs\SendPaymentApprovedEmail;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_a_payment_for_their_own_sent_invoice(): void
    {
        $customerUser = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id, 'status' => 'sent', 'total' => 250]);

        $this->actingAs($customerUser)
            ->post(route('payments.store', $invoice), [
                'method' => 'mpesa',
                'reference' => 'QWE123XYZ',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'method' => 'mpesa',
            'reference' => 'QWE123XYZ',
            'amount' => 250.00,
            'status' => 'pending',
        ]);
    }

    public function test_customer_cannot_submit_a_payment_for_another_customers_invoice(): void
    {
        $customerUser = User::factory()->create(['role' => 'customer']);
        Customer::factory()->create(['user_id' => $customerUser->id]);
        $otherCustomer = Customer::factory()->create();
        $invoice = Invoice::factory()->create(['customer_id' => $otherCustomer->id, 'status' => 'sent']);

        $this->actingAs($customerUser)
            ->post(route('payments.store', $invoice), [
                'method' => 'mpesa',
                'reference' => 'ABC123',
            ])
            ->assertForbidden();
    }

    public function test_customer_cannot_submit_a_payment_for_a_draft_invoice(): void
    {
        $customerUser = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id, 'status' => 'draft']);

        $this->actingAs($customerUser)
            ->post(route('payments.store', $invoice), [
                'method' => 'mpesa',
                'reference' => 'ABC123',
            ])
            ->assertSessionHasErrors('reference');
    }

    public function test_accountant_approving_a_payment_marks_the_invoice_paid_and_emails_the_customer(): void
    {
        Bus::fake();
        $accountant = User::factory()->create(['role' => 'accountant']);
        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id, 'status' => 'sent']);
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $invoice->total,
            'method' => 'card',
            'reference' => 'TXN-1',
            'status' => 'pending',
        ]);

        $this->actingAs($accountant)
            ->patch(route('payments.approve', $payment))
            ->assertRedirect();

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'approved']);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid']);
        Bus::assertDispatched(SendPaymentApprovedEmail::class, fn ($job) => $job->invoice->is($invoice));
    }

    public function test_accountant_rejecting_a_payment_leaves_the_invoice_unpaid(): void
    {
        $accountant = User::factory()->create(['role' => 'accountant']);
        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id, 'status' => 'sent']);
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $invoice->total,
            'method' => 'card',
            'reference' => 'TXN-2',
            'status' => 'pending',
        ]);

        $this->actingAs($accountant)
            ->patch(route('payments.reject', $payment), ['rejection_reason' => 'Reference could not be verified.'])
            ->assertRedirect();

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'rejected']);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'sent']);
    }

    public function test_admin_and_staff_cannot_approve_payments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $invoice = Invoice::factory()->create(['status' => 'sent']);
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $invoice->total,
            'method' => 'card',
            'reference' => 'TXN-3',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->patch(route('payments.approve', $payment))->assertForbidden();
        $this->actingAs($staff)->patch(route('payments.approve', $payment))->assertForbidden();
    }

    public function test_admin_can_view_payments_but_not_review_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('payments.index'))
            ->assertOk();
    }

    public function test_accountant_recording_a_payment_is_self_approved_immediately(): void
    {
        Bus::fake();
        $accountant = User::factory()->create(['role' => 'accountant']);
        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id, 'status' => 'sent', 'total' => 400]);

        $this->actingAs($accountant)
            ->post(route('payments.store', $invoice), [
                'method' => 'mpesa',
                'reference' => 'CASH-1001',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 400.00,
            'status' => 'approved',
            'reviewed_by' => $accountant->id,
        ]);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid']);
        Bus::assertDispatched(SendPaymentApprovedEmail::class);
    }

    public function test_admin_and_staff_cannot_record_payments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $invoice = Invoice::factory()->create(['status' => 'sent']);

        $this->actingAs($admin)
            ->post(route('payments.store', $invoice), ['method' => 'mpesa', 'reference' => 'X'])
            ->assertForbidden();

        $this->actingAs($staff)
            ->post(route('payments.store', $invoice), ['method' => 'mpesa', 'reference' => 'X'])
            ->assertForbidden();
    }
}

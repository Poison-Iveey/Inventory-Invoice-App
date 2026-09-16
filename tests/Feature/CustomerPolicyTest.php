<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_create_customers()
    {
        $user = User::factory()->create(['role' => 'staff']);

        $this->actingAs($user)
            ->post(route('customers.store'), [
                'name' => 'Acme Corp',
                'email' => 'contact@acme.com',
                'phone' => '555-0100',
                'address' => '123 Main St',
            ])
            ->assertRedirect(route('customers.index'));
    }

    public function test_creating_a_customer_does_not_create_a_login_account(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->post(route('customers.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '0712 345 678',
            'address' => 'Nairobi',
        ]);

        $customer = Customer::where('email', 'jane@example.com')->firstOrFail();
        $this->assertNull($customer->user_id);
        $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
    }

    public function test_admin_cannot_create_customers()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('customers.create'))
            ->assertStatus(403);
    }

    public function test_customer_cannot_create_customers()
    {
        $user = User::factory()->create(['role' => 'customer']);
        
        $this->actingAs($user)
            ->get(route('customers.create'))
            ->assertStatus(403);
    }

    public function test_accountant_cannot_create_customers()
    {
        $user = User::factory()->create(['role' => 'accountant']);
        
        $this->actingAs($user)
            ->get(route('customers.create'))
            ->assertStatus(403);
    }

    public function test_admin_can_view_customers()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::factory()->create();
        
        $this->actingAs($admin)
            ->get(route('customers.show', $customer))
            ->assertStatus(200);
    }

    public function test_staff_can_update_customers()
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $customer = Customer::factory()->create();

        $this->actingAs($staff)
            ->put(route('customers.update', $customer), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'phone' => '555-0200',
                'address' => '456 Oak Ave',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_staff_can_delete_customers()
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $customer = Customer::factory()->create();

        $this->actingAs($staff)
            ->delete(route('customers.destroy', $customer))
            ->assertRedirect();

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_admin_cannot_update_or_delete_customers()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::factory()->create();

        $this->actingAs($admin)
            ->put(route('customers.update', $customer), ['name' => 'x', 'email' => $customer->email])
            ->assertStatus(403);

        $this->actingAs($admin)
            ->delete(route('customers.destroy', $customer))
            ->assertStatus(403);
    }
}

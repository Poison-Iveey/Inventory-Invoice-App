<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Notifications\AccountCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_can_access_user_management(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_administrator_can_create_staff_account(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Sales User',
                'email' => 'sales@example.com',
                'role' => 'staff',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'sales@example.com',
            'role' => 'staff',
        ]);

        $user = User::where('email', 'sales@example.com')->firstOrFail();
        Notification::assertSentTo($user, AccountCreated::class);
    }

    public function test_creating_a_user_sets_an_unusable_password_and_emails_a_setup_link(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Sales User',
            'email' => 'sales@example.com',
            'role' => 'staff',
        ]);

        $user = User::where('email', 'sales@example.com')->firstOrFail();

        // the admin never chooses or knows this password — it must not be a
        // guessable/default value like "password".
        $this->assertFalse(Hash::check('password', $user->password));

        Notification::assertSentTo($user, AccountCreated::class);
    }

    public function test_administrator_cannot_set_or_change_a_users_password_on_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $originalPassword = $staff->password;

        $this->actingAs($admin)
            ->put(route('users.update', $staff), [
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => 'staff',
                'password' => 'whatever-i-want',
                'password_confirmation' => 'whatever-i-want',
            ])
            ->assertRedirect(route('users.index'));

        $staff->refresh();
        $this->assertSame($originalPassword, $staff->password);
    }

    public function test_administrator_can_resend_the_setup_email(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($admin)
            ->post(route('users.resend-invite', $staff))
            ->assertRedirect();

        Notification::assertSentTo($staff, AccountCreated::class);
    }

    public function test_every_staff_account_can_manage_products_and_invoices(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $accountant = User::factory()->create(['role' => 'accountant']);
        $product = Product::factory()->create();

        $this->actingAs($staff)->get(route('products.create'))->assertOk();
        $this->actingAs($staff)->get(route('invoices.create'))->assertOk();
        $this->actingAs($staff)->get(route('products.show', $product))->assertOk();

        // admin and accountant are read-only over business records now
        $this->actingAs($accountant)->get(route('products.create'))->assertForbidden();
    }

    public function test_administrator_can_link_a_customer_user_to_a_customer_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::factory()->create();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Customer Login',
                'email' => 'customer@example.com',
                'role' => 'customer',
                'customer_id' => $customer->id,
            ])
            ->assertRedirect(route('users.index'));

        $user = User::where('email', 'customer@example.com')->firstOrFail();
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'user_id' => $user->id,
        ]);
    }
}

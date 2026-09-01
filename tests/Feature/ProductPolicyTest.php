<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_products()
    {
        $user = User::factory()->create(['role' => 'admin']);
        
        $this->actingAs($user)
            ->post(route('products.store'), [
                'sku' => 'TEST-001',
                'name' => 'Test Product',
                'description' => 'Test',
                'price' => 99.99,
                'stock' => 10,
            ])
            ->assertRedirect(route('products.index'));
    }

    public function test_staff_can_create_products()
    {
        $user = User::factory()->create(['role' => 'staff']);
        
        $this->actingAs($user)
            ->post(route('products.store'), [
                'sku' => 'TEST-002',
                'name' => 'Staff Product',
                'description' => 'Test',
                'price' => 49.99,
                'stock' => 5,
            ])
            ->assertRedirect(route('products.index'));
    }

    public function test_customer_cannot_create_products()
    {
        $user = User::factory()->create(['role' => 'customer']);
        
        $this->actingAs($user)
            ->get(route('products.create'))
            ->assertStatus(403);
    }

    public function test_accountant_cannot_create_products()
    {
        $user = User::factory()->create(['role' => 'accountant']);
        
        $this->actingAs($user)
            ->get(route('products.create'))
            ->assertStatus(403);
    }

    public function test_admin_can_update_products()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();
        
        $this->actingAs($admin)
            ->put(route('products.update', $product), [
                'sku' => 'UPDATED',
                'name' => 'Updated Product',
                'description' => 'Updated',
                'price' => 199.99,
                'stock' => 20,
            ])
            ->assertRedirect();
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
        ]);
    }

    public function test_admin_can_delete_products()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();
        
        $this->actingAs($admin)
            ->delete(route('products.destroy', $product))
            ->assertRedirect();
        
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_customer_cannot_update_products()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create();
        
        $this->actingAs($customer)
            ->put(route('products.update', $product), [
                'sku' => 'TEST',
                'name' => 'Hacked',
                'description' => 'test',
                'price' => 99.99,
                'stock' => 10,
            ])
            ->assertStatus(403);
    }
}

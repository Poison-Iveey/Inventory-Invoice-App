<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        // customers with their own login, so we can demo "customers only see their own invoices"
        $alpha = Customer::create([
            'name' => 'Alpha Retail Co',
            'email' => 'alpha@demo.test',
            'phone' => '+1 555 010 3344',
            'address' => '18 Baker Street',
        ]);

        User::create([
            'name' => 'Alpha Retail Co',
            'email' => 'customer@demo.test',
            'password' => $password,
            'role' => 'customer',
            'email_verified_at' => now(),
        ])->customer()->save($alpha);

        $beta = Customer::create([
            'name' => 'Beta Wholesale Ltd',
            'email' => 'beta@demo.test',
            'phone' => '+1 555 010 5566',
            'address' => '9 Riverside Avenue',
        ]);

        User::create([
            'name' => 'Beta Wholesale Ltd',
            'email' => 'customer2@demo.test',
            'password' => $password,
            'role' => 'customer',
            'email_verified_at' => now(),
        ])->customer()->save($beta);

        // customers Admin manages directly, with no login of their own
        Customer::create([
            'name' => 'Green Valley Store',
            'email' => 'orders@greenvalleystore.test',
            'phone' => '+1 555 010 7788',
            'address' => '241 Orchard Road',
        ]);

        Customer::create([
            'name' => 'Metro Traders',
            'email' => 'accounts@metrotraders.test',
            'phone' => '+1 555 010 9900',
            'address' => '77 Industrial Way',
        ]);
    }
}

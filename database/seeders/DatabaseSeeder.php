<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Fill the database with demo accounts and sample records so the app
     * can be logged into and demoed straight away.
     */
    public function run(): void
    {
        $this->call(RoleUserSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(InvoiceSeeder::class);
    }
}

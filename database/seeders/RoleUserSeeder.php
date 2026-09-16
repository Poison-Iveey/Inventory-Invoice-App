<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleUserSeeder extends Seeder
{
    /**
     * Creates one login for each role so every part of the app can be
     * tried out. Everyone uses the same password to keep the demo simple.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        User::create([
            'name' => 'Amina Okafor',
            'email' => 'admin@demo.test',
            'password' => $password,
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Brian Otieno',
            'email' => 'staff@demo.test',
            'password' => $password,
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Carla Mensah',
            'email' => 'accountant@demo.test',
            'password' => $password,
            'role' => 'accountant',
            'email_verified_at' => now(),
        ]);

        // customer logins are created in CustomerSeeder, once the
        // matching customer record exists to link them to
    }
}

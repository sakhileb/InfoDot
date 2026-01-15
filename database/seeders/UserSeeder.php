<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@infodot.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create test users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@infodot.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@infodot.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@infodot.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Alice Williams',
            'email' => 'alice@infodot.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create additional random users
        User::factory(10)->create();
    }
}

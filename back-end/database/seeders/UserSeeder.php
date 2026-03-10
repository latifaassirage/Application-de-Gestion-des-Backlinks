<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        User::firstOrCreate(
            ['email' => 'admin@agency.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
            ]
        );

        // Create Staff User
        User::firstOrCreate(
            ['email' => 'staff@agency.com'],
            [
                'name' => 'Staff User',
                'password' => bcrypt('staff123'),
                'role' => 'staff',
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;
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

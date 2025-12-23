<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Asset;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create test user
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password123'),
                'balance' => 10000.00, // USD balance
            ]
        );

        User::updateOrCreate(
            ['email' => 'test2@example.com'],
            [
                'name' => 'Test User 2',
                'password' => Hash::make('password123'),
                'balance' => 15000.00, // USD balance
            ]
        );
    }
}

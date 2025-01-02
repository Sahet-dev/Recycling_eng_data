<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
{
    // Create an admin user
    User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.co',
        'password' => Hash::make('password'),
        'role' => 'admin'
    ]);

    // Create a regular user
    User::create([
        'name' => 'Regular User',
        'email' => 'user1@example.com',
        'password' => Hash::make('password'),
        'role' => 'user'
    ]);
}
}

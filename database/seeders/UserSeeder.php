<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'first_name' => 'Admin',
            'last_name' => 'User',
            'mobile' => '1234567890',
            'location' => 'Admin Location',
            'image' => 'users/admin.jpg',
            'role' => 'admin',
        ]);

        // Create test customers
        User::create([
            'first_name' => 'Test',
            'last_name' => 'User1',
            'mobile' => '0100123456',
            'location' => 'Cairo, Egypt',
            'image' => 'users/user1.jpg',
            'role' => 'user',
        ]);

        User::create([
            'first_name' => 'Test',
            'last_name' => 'User2',
            'mobile' => '0100234567',
            'location' => 'Alexandria, Egypt',
            'image' => 'users/user2.jpg',
            'role' => 'user',
        ]);

        User::create([
            'first_name' => 'Test',
            'last_name' => 'User3',
            'mobile' => '0100345678',
            'location' => 'Giza, Egypt',
            'image' => 'users/user3.jpg',
            'role' => 'user',
        ]);

        // Create 20 more test users
        User::factory(20)->create();

        $this->command->info('Users created successfully!');
    }
}

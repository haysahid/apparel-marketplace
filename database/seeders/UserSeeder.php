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
        User::insert([
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('superadmin2025'),
                'role_id' => 1,
            ],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin2025'),
                'role_id' => 2,
            ],
            [
                'name' => 'User',
                'username' => 'user',
                'email' => 'user@example.com',
                'password' => Hash::make('user2025'),
                'role_id' => 3,
            ],
            [
                'name' => 'Admin Mustika Collection',
                'username' => 'adminmustika',
                'email' => 'adminmustika@example.com',
                'password' => Hash::make('adminmustika2025'),
                'role_id' => 3,
            ],
        ]);
    }
}

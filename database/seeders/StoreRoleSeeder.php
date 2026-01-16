<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assign users to store with roles
        $superAdmin = User::where('username', 'superadmin')->first();
        $superAdmin->stores()->attach(1, [
            'role_id' => 1, // Super Admin role
            'created_at' => now(),
        ]);

        $admin = User::where('username', 'admin')->first();
        $admin->stores()->attach(1, [
            'role_id' => 2, // Admin role
            'created_at' => now(),
        ]);

        $storeAdmin = User::where('username', 'pemiliktoko')->first();
        $storeAdmin->stores()->attach(1, [
            'role_id' => 4, // Store Owner role
            'created_at' => now(),
        ]);

        $employee = User::where('username', 'karyawantoko')->first();
        $employee->stores()->attach(1, [
            'role_id' => 5, // Employee role
            'created_at' => now(),
        ]);
    }
}

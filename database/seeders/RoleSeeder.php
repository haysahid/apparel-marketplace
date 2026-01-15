<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::insert([
            ['name' => 'Super Admin', 'slug' => 'super-admin'],
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'User', 'slug' => 'user'],
            ['name' => 'Agen', 'slug' => 'agent'],
            ['name' => 'Reseller', 'slug' => 'reseller'],
            ['name' => 'Member', 'slug' => 'member'],
            ['name' => 'Pemilik Toko', 'slug' => 'store-owner'],
            ['name' => 'Karyawan', 'slug' => 'employee'],
            ['name' => 'Tamu', 'slug' => 'guest'],
        ]);
    }
}

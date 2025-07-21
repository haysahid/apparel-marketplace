<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            RoleSeeder::class,
            PlatformSeeder::class,
            UserSeeder::class,
            StoreSeeder::class,
            ProductSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}

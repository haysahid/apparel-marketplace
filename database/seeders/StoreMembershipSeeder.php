<?php

namespace Database\Seeders;

use App\Models\StoreMembership;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreMembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StoreMembership::factory()->count(5)->create([
            'store_id' => 1,
        ]);
    }
}

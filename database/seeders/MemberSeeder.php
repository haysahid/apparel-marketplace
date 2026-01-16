<?php

namespace Database\Seeders;

use App\Models\StoreMembership;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StoreMembership::create([
            'name' => 'Basic Membership',
            'description' => 'Basic membership with limited features.',
            'price' => 9.99,
            'duration_days' => 30,
        ]);
    }
}

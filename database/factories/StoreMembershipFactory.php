<?php

namespace Database\Factories;

use App\Models\MembershipType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoreMembership>
 */
class StoreMembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $membershipTypeId = MembershipType::inRandomOrder()->first()->id;

        return [
            'store_id' => 1,
            'user_id' => User::factory(),
            'membership_type_id' => $membershipTypeId,
        ];
    }
}

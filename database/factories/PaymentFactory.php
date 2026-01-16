<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => null,
            'payment_method_id' => null,
            'amount' => fake()->numberBetween(50000, 500000),
            'note' => fake()->optional()->sentence(),
            'reason' => null,
            'image' => null,
            'midtrans_snap_token' => null,
            'midtrans_response' => null,
            'status' => 'pending',
        ];
    }
}

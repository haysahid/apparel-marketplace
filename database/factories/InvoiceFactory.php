<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseAmount = fake()->numberBetween(50000, 500000);
        $shippingCost = fake()->numberBetween(5000, 50000);
        $tax = 0;

        return [
            'store_id' => 1,
            'transaction_id' => null,
            'user_id' => null,
            'code' => strtoupper(fake()->unique()->bothify('INV-' . '####??')),
            'description' => fake()->optional()->sentence(),
            'base_amount' => $baseAmount,
            'shipping_cost' => $shippingCost,
            'tax' => $tax,
            'voucher_id' => null,
            'voucher_amount' => 0,
            'amount' => $baseAmount + $shippingCost + $tax,
            'due_date' => fake()->dateTimeBetween('+1 days', '+2 days'),
            'paid_at' => null,
            'shipping_estimate' => fake()->numberBetween(1, 7) . ' days',
            'shipped_at' => null,
            'picked_up_at' => null,
            'delivered_at' => null,
            'status' => 'pending',
            'created_at' => fake()->dateTimeBetween('-6 months', '-1 month'),
        ];
    }
}

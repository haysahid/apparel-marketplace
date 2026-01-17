<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShipmentItem>
 */
class ShipmentItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipment_id' => null, // to be set when creating shipment
            'transaction_item_id' => null, // to be set when creating shipment item
            'shipped_quantity' => $this->faker->numberBetween(1, 5),
        ];
    }
}

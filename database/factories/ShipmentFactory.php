<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\ShipmentStatus;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shipment>
 */
class ShipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => null,
            'invoice_id' => null,
            'tracking_number' => $this->faker->unique()->bothify('TRK##########'),
            'courier_name' => $this->faker->randomElement(['jne', 'tiki', 'pos', 'sicepat', 'jnt']),
            'weight' => $this->faker->numberBetween(100, 5000), // weight in grams
            'shipping_cost' => $this->faker->numberBetween(5000, 50000), // cost in cents
            'status' => $this->faker->randomElement(ShipmentStatus::cases())->value,
        ];
    }
}

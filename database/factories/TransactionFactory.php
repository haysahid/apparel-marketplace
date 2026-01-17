<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PaymentStatus;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\ShipmentStatus;
use App\Models\ShippingMethod;
use App\Models\TransactionItem;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $transactionType = TransactionType::where('slug', 'sale')->first();
        $paymentMethod = PaymentMethod::inRandomOrder()->first();
        $shippingMethod = ShippingMethod::inRandomOrder()->first();
        $createdAt = fake()->dateTimeBetween('-6 months', 'now');

        $status = null;
        // If created in the last 14 days, pick any status
        // with 20% pending, 20% paid, 30% processing, 25% completed, 5% cancelled
        if ($createdAt >= now()->subDays(14)) {
            $rand = $this->faker->numberBetween(1, 100);
            if ($rand <= 20) {
                $status = TransactionStatus::PENDING->value;
            } elseif ($rand <= 40) {
                $status = TransactionStatus::PAID->value;
            } elseif ($rand <= 70) {
                $status = TransactionStatus::PROCESSING->value;
            } elseif ($rand <= 95) {
                $status = TransactionStatus::COMPLETED->value;
            } else {
                $status = TransactionStatus::CANCELLED->value;
            }
        } else {
            // Otherwise, limit to completed or cancelled
            // with 80% chance to be completed
            $status = $this->faker->boolean(80)
                ? TransactionStatus::COMPLETED->value
                : TransactionStatus::CANCELLED->value;
        }

        $data = [
            'user_id' => User::factory(),
            'type_id' => $transactionType->id,
            'code' => strtoupper(fake()->unique()->bothify($transactionType->code_prefix . '-' . '####??')),
            'note' => fake()->sentence(),
            'payment_method_id' => $paymentMethod->id,
            'shipping_method_id' => $shippingMethod->id,
            'voucher_id' => null,
            'voucher_amount' => 0,
            'shipping_cost' => 0,
            'paid_at' => null,
            'status' => $status,
            'created_at' => $createdAt,
        ];

        if ($shippingMethod->slug === 'courier') {
            $data['rajaongkir_destination_id'] = fake()->numberBetween(1, 1000);
            $data['province_name'] = fake()->state();
            $data['city_name'] = fake()->city();
            $data['district_name'] = fake()->word();
            $data['subdistrict_name'] = fake()->word();
            $data['zip_code'] = fake()->postcode();
            $data['address'] = fake()->address();
            $data['shipping_cost'] = fake()->numberBetween(5000, 50000);
        }

        return $data;
    }

    public function configure()
    {
        return $this->afterCreating(function ($transaction) {
            $shippingEstimate = null;
            $shippedAt = null;
            $deliveredAt = null;
            $pickedUpAt = null;

            // Handle logic for 'courier' shipping method
            if ($transaction->shipping_method && $transaction->shipping_method->slug === 'courier') {
                $shippingEstimate = fake()->numberBetween(1, 7) . ' days';

                // Payment method logic
                if ($transaction->payment_method && $transaction->payment_method->slug === 'transfer') {
                    // If status is paid or beyond, set paid_at between 0 - 1 days after created_at
                    if (in_array(
                        $transaction->status,
                        [
                            TransactionStatus::PAID->value,
                            TransactionStatus::PROCESSING->value,
                            TransactionStatus::COMPLETED->value
                        ]
                    )) {
                        $transaction->paid_at = $this->faker
                            ->dateTimeBetween($transaction->created_at, (clone $transaction->created_at)->modify('+1 days'));
                        $transaction->save();
                    }
                } elseif ($transaction->payment_method && $transaction->payment_method->slug === 'cod') {
                    // For COD, payment is made at the same time as completed
                    if ($transaction->status === TransactionStatus::COMPLETED->value) {
                        $transaction->paid_at = $deliveredAt ?? $transaction->created_at;
                        $transaction->save();
                    }
                }

                // If status is processing or beyond, set shipped_at between paid_at - (paid_at + 2 days), but before shipping_estimate
                if (in_array(
                    $transaction->status,
                    [
                        TransactionStatus::PROCESSING->value,
                        TransactionStatus::COMPLETED->value
                    ]
                )) {
                    $startDate = $transaction->paid_at ?? $transaction->created_at;
                    // Extract days from shipping_estimate string (e.g., "3 days")
                    $estimateDays = 7;
                    if ($shippingEstimate) {
                        preg_match('/\d+/', $shippingEstimate, $matches);
                        $estimateDays = isset($matches[0]) ? (int)$matches[0] : 7;
                    }
                    $maxShippedAt = (clone $startDate)->modify('+' . ($estimateDays - 1) . ' days');
                    $endDate = (clone $startDate)->modify('+2 days');
                    // Ensure shipped_at does not exceed shipping_estimate
                    if ($endDate > $maxShippedAt) {
                        $endDate = $maxShippedAt;
                    }
                    $shippedAt = $this->faker->dateTimeBetween($startDate, $endDate);
                }

                // If status is completed, set delivered_at after shipped_at, up to shipping_estimate
                if ($transaction->status === TransactionStatus::COMPLETED->value) {
                    $startDate = $shippedAt ?? ($transaction->paid_at ?? $transaction->created_at);
                    $estimateDays = 7;
                    if ($shippingEstimate) {
                        preg_match('/\d+/', $shippingEstimate, $matches);
                        $estimateDays = isset($matches[0]) ? (int)$matches[0] : 7;
                    }
                    $endDate = (clone $transaction->paid_at ?? $transaction->created_at)->modify('+' . $estimateDays . ' days');
                    $deliveredAt = $this->faker->dateTimeBetween($startDate, $endDate);

                    // For COD, override set paid_at at delivered_at
                    if ($transaction->payment_method && $transaction->payment_method->slug === 'cod') {
                        $transaction->paid_at = $deliveredAt;
                        $transaction->save();
                    }
                }
            }

            // Handle logic for 'pickup' shipping method
            if ($transaction->shipping_method && $transaction->shipping_method->slug === 'pickup') {
                // Payment method logic
                if ($transaction->payment_method && $transaction->payment_method->slug === 'transfer') {
                    // If status is paid or beyond, set paid_at between 0 - 1 days after created_at
                    if (in_array(
                        $transaction->status,
                        [
                            TransactionStatus::PAID->value,
                            TransactionStatus::PROCESSING->value,
                            TransactionStatus::COMPLETED->value
                        ]
                    )) {
                        $transaction->paid_at = $this->faker
                            ->dateTimeBetween($transaction->created_at, (clone $transaction->created_at)->modify('+1 days'));
                        $transaction->save();
                    }
                } elseif ($transaction->payment_method && $transaction->payment_method->slug === 'cod') {
                    // For COD, paid_at is set at picked_up_at (when picked up)
                    if (in_array(
                        $transaction->status,
                        [
                            TransactionStatus::PAID->value,
                            TransactionStatus::PROCESSING->value,
                            TransactionStatus::COMPLETED->value
                        ]
                    )) {
                        // We'll set paid_at after picked_up_at is generated below
                        // So just leave it for now
                    }
                }

                // If status is completed, set picked_up_at and delivered_at (delivered_at = picked_up_at for pickup)
                if ($transaction->status === TransactionStatus::COMPLETED->value) {
                    $startDate = $transaction->paid_at ?? $transaction->created_at;
                    $pickedUpAt = $this->faker
                        ->dateTimeBetween($startDate, (clone $startDate)->modify('+7 days'));
                    $deliveredAt = $pickedUpAt;

                    // For COD, set paid_at at picked_up_at
                    if ($transaction->payment_method && $transaction->payment_method->slug === 'cod') {
                        $transaction->paid_at = $pickedUpAt;
                        $transaction->save();
                    }
                }
            }

            // Create transaction items, invoice, and payment
            $items = $this->withItems($transaction);
            $this->withInvoice(
                transaction: $transaction,
                items: $items,
                shippingEstimate: $shippingEstimate,
                shippedAt: $shippedAt,
                deliveredAt: $deliveredAt,
                pickedUpAt: $pickedUpAt
            );
            $this->withPayment($transaction, $items);
        });
    }

    // Transaction items
    public function withItems($transaction)
    {
        return TransactionItem::factory()->count(rand(1, 5))->create([
            'transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'fullfillment_status' => $transaction->status,
            'created_at' => $transaction->created_at,
        ]);
    }

    // Invoices
    public function withInvoice(
        $transaction,
        $items,
        $shippingEstimate = null,
        $shippedAt = null,
        $deliveredAt = null,
        $pickedUpAt = null
    ) {
        $finalAmount = $items->sum('subtotal') + $transaction->shipping_cost;

        $invoice = Invoice::factory()->create([
            'transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'base_amount' => $items->sum('subtotal'),
            'shipping_cost' => $transaction->shipping_cost,
            'amount' => $finalAmount,
            'paid_at' => $transaction->paid_at,
            'shipping_estimate' => $shippingEstimate,
            'shipped_at' => $shippedAt,
            'picked_up_at' => $pickedUpAt,
            'delivered_at' => $deliveredAt,
            'status' => $transaction->status,
            'created_at' => $transaction->created_at,
        ]);

        // Add shipment if shipping method is courier
        if ($transaction->shipping_method && $transaction->shipping_method->slug === 'courier') {
            $shipment = Shipment::factory()->create([
                'store_id' => $invoice->store_id,
                'invoice_id' => $invoice->id,
                'shipping_cost' => $transaction->shipping_cost,
                'status' => $deliveredAt ? ShipmentStatus::DELIVERED->value : ($shippedAt ? ShipmentStatus::IN_TRANSIT->value : ShipmentStatus::PENDING->value),
            ]);

            // Create shipment items
            foreach ($items as $item) {
                ShipmentItem::factory()->create([
                    'shipment_id' => $shipment->id,
                    'transaction_item_id' => $item->id,
                    'shipped_quantity' => $item->quantity,
                ]);
            }
        }
    }

    // Payments
    public function withPayment($transaction, $items)
    {
        $finalAmount = $items->sum('subtotal') + $transaction->shipping_cost;

        Payment::factory()->create([
            'transaction_id' => $transaction->id,
            'payment_method_id' => $transaction->payment_method_id,
            'amount' => $finalAmount,
            'status' => $transaction->paid_at ? PaymentStatus::COMPLETED->value : PaymentStatus::PENDING->value,
        ]);
    }
}

<?php

namespace App\Repositories;

class MidtransRepository
{
    public function __construct()
    {
        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }

    public static function getPaymentMethods(): array
    {
        $banks = include database_path('seeders/data/banks.php');
        return [
            'bank_transfer' => array_values($banks),
        ];
    }

    public static function createSnapToken($orderId, $itemDetails, $customer, $grossAmount)
    {
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $customer->name,
                'last_name' => '',
                'email' => $customer->email,
                'phone' => $customer->phone,
            ],
        ];

        return \Midtrans\Snap::getSnapToken($params);
    }

    public static function getTransactionStatus($transactionCode): object
    {
        return (object) \Midtrans\Transaction::status($transactionCode);
    }
}

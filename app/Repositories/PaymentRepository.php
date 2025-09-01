<?php

namespace App\Repositories;

use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentRepository
{
    public function createPayment(array $data): Payment
    {
        try {
            $payment = Payment::create($data);
            return $payment;
        } catch (Exception $e) {
            Log::error('Error creating payment: ' . $e);
            throw new Exception('Gagal membuat pembayaran: ' . $e);
        }
    }
}

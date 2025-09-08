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

    public function setComplete(Payment $payment): Payment
    {
        try {
            $payment->status = 'completed';
            $payment->save();

            return $payment;
        } catch (Exception $e) {
            Log::error('Gagal mengubah status pembayaran menjadi dibayar: ' . $e);
            throw new Exception('Gagal mengubah status pembayaran menjadi dibayar: ' . $e);
        }
    }
}

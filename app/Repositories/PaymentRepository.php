<?php

namespace App\Repositories;

use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentRepository
{
    public static function createPayment(array $data): Payment
    {
        try {
            $payment = Payment::create($data);
            return $payment;
        } catch (Exception $e) {
            Log::error('Error creating payment: ' . $e);
            throw new Exception('Gagal membuat pembayaran: ' . $e);
        }
    }

    public static function setComplete(Payment $payment): Payment
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

    public static function setFailed(Payment $payment): Payment
    {
        try {
            $payment->status = 'failed';
            $payment->save();

            return $payment;
        } catch (Exception $e) {
            Log::error('Gagal mengubah status pembayaran menjadi gagal: ' . $e);
            throw new Exception('Gagal mengubah status pembayaran menjadi gagal: ' . $e);
        }
    }

    public static function getPayments(
        int $storeId,
        int $limit = 10,
        ?string $search = null,
        string $orderBy = 'created_at',
        string $orderDirection = 'desc',
        ?int $typeId = null,
    ) {
        $query = Payment::query()
            ->with([
                'transaction',
                'transaction.type',
                'transaction.user',
                'payment_method'
            ]);

        // if ($storeId) {
        //     $query->whereHas('transaction', function ($q) use ($storeId) {
        //         $q->where('store_id', $storeId);
        //     });
        // }

        if ($typeId) {
            $query->whereHas('transaction', function ($q) use ($typeId) {
                $q->where('type_id', $typeId);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', '%' . $search . '%')
                    ->orWhere('reason', 'like', '%' . $search . '%')
                    ->orWhereHas('transaction', function ($q2) use ($search) {
                        $q2->where('code', 'like', '%' . $search . '%')
                            ->orWhereHas('user', function ($q3) use ($search) {
                                $q3->where('name', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        $allowedOrderFields = ['created_at', 'amount', 'status'];
        if (!in_array($orderBy, $allowedOrderFields)) {
            $orderBy = 'created_at';
        }

        if (!in_array(strtolower($orderDirection), ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        return $query->orderBy($orderBy, $orderDirection)
            ->paginate($limit)
            ->withQueryString();
    }
}

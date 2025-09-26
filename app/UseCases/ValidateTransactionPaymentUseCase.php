<?php

namespace App\UseCases;

use App\Core\DataState;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Transaction;
use App\Repositories\MidtransRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\TransactionRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ValidateTransactionPaymentUseCase
{
    private TransactionRepository $transactionRepository;
    private PaymentRepository $paymentRepository;
    private MidtransRepository $midtransRepository;

    public function __construct()
    {
        $this->transactionRepository = new TransactionRepository();
        $this->paymentRepository = new PaymentRepository();
        $this->midtransRepository = new MidtransRepository();
    }

    public function validate($transactionCode): DataState
    {
        try {
            $transaction = Transaction::with(['payments'])->where('code', $transactionCode)->first();
            $payment = Payment::where('transaction_id', $transaction->id)->latest()->first();

            if (!$payment) {
                return DataState::error('Pembayaran tidak ditemukan untuk transaksi ini.', 404);
            }

            $transactionCode = $transaction->code;

            if ($transaction->payments->count() > 1) {
                $transactionCode = $transaction->code . '-' . ($transaction->payments->count() - 1);
            }

            if ($payment->payment_method->slug == 'transfer') {
                $paymentStatusBefore = $payment->status;

                try {
                    $response = $this->midtransRepository->getTransactionStatus($transactionCode);
                    if ($response->transaction_status == 'settlement') {
                        $paymentStatusAfter = 'completed';
                    } else if ($response->transaction_status == 'failed' || $response->transaction_status == 'expire') {
                        $paymentStatusAfter = 'failed';
                    } else {
                        $paymentStatusAfter = 'pending';
                    }
                } catch (Exception $e) {
                    if ($e->getCode() == 404) {
                        $paymentStatusAfter = 'failed';
                    }
                }

                // Update payment, invoice, and transaction status if needed
                if ($paymentStatusBefore !== 'completed' || $transaction->status === 'pending') {
                    if ($paymentStatusAfter === 'completed') {
                        $payment = $this->paymentRepository->setComplete($payment);
                        $this->transactionRepository->setPaidNow($transaction);
                    } else if ($paymentStatusAfter === 'failed') {
                        $payment = $this->paymentRepository->setFailed($payment);
                        $this->transactionRepository->setCancelled($transaction);
                    }
                }
            }

            return DataState::success($payment);
        } catch (Exception $e) {
            Log::error('Gagal memeriksa status pembayaran: ' . $e->getMessage());
            return DataState::error('Gagal memeriksa status pembayaran: ' . $e->getMessage());
        }
    }
}

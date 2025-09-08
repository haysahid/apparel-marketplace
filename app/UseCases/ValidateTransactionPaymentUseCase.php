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
            $payment = Payment::where('transaction_id', $transaction->id)->first();

            if (!$payment) {
                return DataState::error('Pembayaran tidak ditemukan untuk transaksi ini.', 404);
            }

            $transactionCode = $transaction->code;

            if ($transaction->payments->count() > 1) {
                $transactionCode = $transaction->code . '-' . ($transaction->payments->count() - 1);
            }

            $response = $this->midtransRepository->getTransactionStatus($transactionCode);

            $paymentStatusBefore = $payment->status;

            // Update payment
            $payment->midtrans_response = json_encode($response);
            $paymentStatusAfter = $response->transaction_status == 'settlement'
                ? 'completed'
                : ($response->transaction_status == 'failed' ? 'failed' : 'pending');

            // Update invoice paid_at if payment is completed
            if ($paymentStatusAfter === 'completed' && ($paymentStatusBefore !== 'completed' || $transaction->status === 'pending')) {
                // Update payment status
                $payment = $this->paymentRepository->setComplete($payment);

                // Update transaction status
                $this->transactionRepository->setPaidNow($transaction);
            }

            $payment->midtrans_response = json_decode($payment->midtrans_response, true);

            return DataState::success($payment);
        } catch (Exception $e) {
            Log::error('Gagal memeriksa status pembayaran: ' . $e->getMessage());
            return DataState::error('Gagal memeriksa status pembayaran: ' . $e->getMessage());
        }
    }
}

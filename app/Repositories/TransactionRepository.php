<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Exception;
use Illuminate\Support\Facades\Log;

class TransactionRepository
{
    public static function getTransactionDetail($transactionCode, $storeId = null)
    {
        $transaction = Transaction::with(['payment_method', 'shipping_method', 'payments'])
            ->where('code', $transactionCode)
            ->firstOrFail();
        $invoices = Invoice::with(['store'])->where(function ($query) use ($transaction, $storeId) {
            $query->where('transaction_id', $transaction->id);
            if ($storeId) {
                $query->where('store_id', $storeId);
            }
        })->get();
        $groups = $invoices->map(function ($invoice) {
            $items = TransactionItem::where('transaction_id', $invoice->transaction_id)
                ->where('store_id', $invoice->store_id)
                ->get();

            return [
                'store_id' => $invoice->store_id,
                'store' => $invoice->store,
                'invoice' => $invoice->makeHidden(['store']),
                'items' => $items,
            ];
        })->filter(function ($item) {
            return $item['items']->isNotEmpty();
        });

        return [
            'transaction' => $transaction,
            'groups' => $groups,
        ];
    }

    public static function createTransaction(array $data): Transaction
    {
        try {
            $transaction = Transaction::create($data);
            return $transaction;
        } catch (Exception $e) {
            Log::error('Error creating transaction: ' . $e);
            throw new Exception('Gagal membuat transaksi: ' . $e);
        }
    }

    public static function createTransactionItem(array $data): TransactionItem
    {
        try {
            $transactionItem = TransactionItem::create($data);
            return $transactionItem;
        } catch (Exception $e) {
            Log::error('Error creating transaction item: ' . $e);
            throw new Exception('Gagal membuat item transaksi: ' . $e);
        }
    }
}

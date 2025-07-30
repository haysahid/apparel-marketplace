<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\TransactionItem;

class TransactionRepository
{
    public static function getTransactionDetail($transactionCode)
    {
        $transaction = Transaction::with(['payment_method', 'shipping_method', 'payments'])
            ->where('code', $transactionCode)
            ->firstOrFail();
        $invoices = Invoice::with(['store'])->where('transaction_id', $transaction->id)->get();
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
}

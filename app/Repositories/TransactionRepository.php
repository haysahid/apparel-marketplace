<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionRepository
{
    public static function getTransactions(
        $storeId = null,
        $limit = 10,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
        $brandId = null,
    ) {
        $transactions = Transaction::query();

        $transactions->with([
            'type',
            'user',
            'payment_method',
            'shipping_method',
            'items.variant.product.brand',
            'invoices' => function ($query) use ($storeId) {
                if ($storeId) {
                    $query->where('store_id', $storeId);
                }
            },
        ]);

        if ($brandId) {
            $transactions->whereHas('items.variant.product.brand', function ($query) use ($brandId) {
                $query->where('id', $brandId);
            });
        }

        if ($search) {
            $transactions->where(function ($query) use ($search) {
                $query->where('code', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('items.variant', function ($q) use ($search) {
                        $q->where('motif', 'like', '%' . $search . '%')
                            ->orWhereHas('color', function ($q) use ($search) {
                                $q->where('name', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('size', function ($q) use ($search) {
                                $q->where('name', 'like', '%' . $search . '%');
                            })
                            ->where('material', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('items.variant.product', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhereHas('brand', function ($q) use ($search) {
                                $q->where('name', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        $transactions->orderBy($orderBy, $orderDirection);
        return $transactions->paginate($limit);
    }

    public static function getTransactionDetail($transactionCode, $storeId = null)
    {
        $transaction = Transaction::with(['user', 'payment_method', 'shipping_method', 'payments'])
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

    public static function setPaidNow(Transaction $transaction): void
    {
        try {
            DB::beginTransaction();

            // Update invoice status
            Invoice::where('transaction_id', $transaction->id)
                ->update([
                    'paid_at' => now(),
                    'status' => 'paid',
                ]);

            // Update transaction status
            $transaction->paid_at = now();
            $transaction->status = 'paid';
            $transaction->save();

            // Update transaction items status
            TransactionItem::where('transaction_id', $transaction->id)
                ->update(['fullfillment_status' => 'paid']);

            // Update stock for each transaction item
            foreach ($transaction->items as $item) {
                $variant = ProductVariant::findOrFail($item->variant_id);
                $variant->current_stock_level -= $item->quantity;
                $variant->save();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal mengubah status transaksi menjadi dibayar: ' . $e);
            throw new Exception('Gagal mengubah status transaksi menjadi dibayar: ' . $e);
        }
    }
}

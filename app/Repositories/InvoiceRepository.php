<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\TransactionItem;

class InvoiceRepository
{
    public static function getInvoices(
        $storeId = null,
        $userId = null,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
        $brandId = null,
    ) {
        $invoices = Invoice::query();

        $invoices->with([
            'store',
            'transaction.user',
            'transaction.payment_method',
            'transaction.shipping_method',
            'transaction.items',
        ]);

        if ($storeId) {
            $invoices->where('store_id', $storeId);
        }

        if ($userId) {
            $invoices->whereHas('transaction', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });
        }

        if ($brandId) {
            $invoices->where(function ($query) use ($brandId) {
                $query->whereHas('transaction', function ($q) use ($brandId) {
                    $q->whereHas('items', function ($q2) use ($brandId) {
                        $q2->where(function ($q3) use ($brandId) {
                            $q3->whereHas('variant.product.brand', function ($q4) use ($brandId) {
                                $q4->where('id', $brandId);
                            });
                        });
                    });
                });
            });
        }

        if ($search) {
            $invoices->where(function ($query) use ($search) {
                $query->where('code', 'like', '%' . $search . '%')
                    ->orWhereHas('transaction', function ($q) use ($search) {
                        $q->whereHas('user', function ($qq) use ($search) {
                            $qq->where('name', 'like', '%' . $search . '%');
                        });
                    });
            });
        }

        return $invoices->orderBy($orderBy, $orderDirection)->paginate($limit);
    }

    public static function getInvoiceDetail($invoiceId)
    {
        $invoice = Invoice::with([
            'store',
            'voucher',
            'transaction',
            'transaction.user',
            'transaction.payment_method',
            'transaction.shipping_method',
        ])->findOrFail($invoiceId);

        $items = TransactionItem::with([
            'variant.product.brand',
            'variant.color',
            'variant.size',
        ])
            ->where('store_id', $invoice->store_id)
            ->where('transaction_id', $invoice->transaction_id)
            ->get();

        $payments = Payment::where('transaction_id', $invoice->transaction_id)->get();

        return [
            'invoice' => $invoice,
            'items' => $items,
            'payments' => $payments,
        ];
    }
}

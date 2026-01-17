<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionStatus;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'voucher',
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
                $query->where('code', 'ilike', '%' . $search . '%')
                    ->orWhereHas('transaction', function ($q) use ($search) {
                        $q->whereHas('user', function ($qq) use ($search) {
                            $qq->where('name', 'ilike', '%' . $search . '%');
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

    public static function createInvoice(array $data): Invoice
    {
        try {
            $invoice = Invoice::create($data);
            return $invoice;
        } catch (Exception $e) {
            Log::error("Gagal membuat invoice: " . $e);
            throw new Exception('Gagal membuat invoice: ' . $e);
        }
    }

    public static function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        try {
            $invoice->update($data);
            return $invoice;
        } catch (Exception $e) {
            Log::error("Gagal memperbarui invoice: " . $e);
            throw new Exception('Gagal memperbarui invoice: ' . $e);
        }
    }

    public static function deleteInvoice(Invoice $invoice): bool
    {
        try {
            return $invoice->delete();
        } catch (Exception $e) {
            Log::error("Gagal menghapus invoice: " . $e);
            throw new Exception('Gagal menghapus invoice: ' . $e);
        }
    }

    public function setDelivering(Invoice $invoice, string $trackingNumber): Invoice
    {
        try {
            DB::beginTransaction();

            // Update invoice status and tracking number
            $invoice->status = TransactionStatus::PROCESSING->value;
            $invoice->tracking_number = $trackingNumber;
            $invoice->shipped_at = now();
            $invoice->save();

            // Update related transaction_items fulfillment status
            TransactionItem::where('store_id', $invoice->store_id)
                ->where('transaction_id', $invoice->transaction_id)
                ->update(['fulfillment_status' => TransactionStatus::PROCESSING->value]);

            // Update related transaction status if needed
            Transaction::whereHas('invoices', function ($query) use ($invoice) {
                $query->where('id', $invoice->id);
            })->update(['status' => TransactionStatus::PROCESSING->value]);

            DB::commit();

            return $invoice->load(['transaction']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Gagal memperbarui status pengiriman invoice: " . $e);
            throw new Exception('Gagal memperbarui status pengiriman invoice: ' . $e);
        }
    }
}

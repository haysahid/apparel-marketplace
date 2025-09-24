<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Repositories\InvoiceRepository;
use App\Repositories\VoucherRepository;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function orderSuccess(Request $request)
    {
        $orderId = $request->query('order_id');
        $transaction_code = $request->query('transaction_code') ?? $orderId;

        if ($transaction_code) {
            $transaction = Transaction::with(['payment_method', 'shipping_method', 'payments', 'user'])
                ->where('code', $transaction_code)
                ->firstOrFail();
            $invoices = Invoice::with(['store', 'voucher'])->where('transaction_id', $transaction->id)->get();
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
        } else {
            Log::error('No invoice or transaction code provided for order success.');
            return redirect()->route('my-order')->with('error', 'Invalid order details.');
        }

        return Inertia::render('OrderSuccess', [
            'transaction' => $transaction,
            'groups' => $groups,
            'isGuest' => false,
        ]);
    }

    public function orderSuccessGuest(Request $request)
    {
        $orderId = $request->query('order_id');
        $transaction_code = $request->query('transaction_code') ?? $orderId;

        if ($transaction_code) {
            $transaction = Transaction::with(['payment_method', 'shipping_method', 'payments', 'user'])
                ->where('code', $transaction_code)
                ->firstOrFail();
            $invoices = Invoice::with(['store', 'voucher'])->where('transaction_id', $transaction->id)->get();
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
        } else {
            Log::error('No invoice or transaction code provided for order success.');
            return redirect()->route('my-order')->with('error', 'Invalid order details.');
        }

        return Inertia::render('OrderSuccess', [
            'transaction' => $transaction,
            'groups' => $groups,
            'isGuest' => true,
        ]);
    }

    public function myOrder(Request $request)
    {
        $limit = $request->input('limit', 5);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');

        $invoices = InvoiceRepository::getInvoices(
            userId: Auth::id(),
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return Inertia::render('MyOrder', [
            'invoices' => $invoices,
        ]);
    }

    public function myOrderDetail(Request $request, $invoice_code)
    {
        $invoice = Invoice::where('code', $invoice_code)->firstOrFail();

        $invoiceDetail = InvoiceRepository::getInvoiceDetail(
            invoiceId: $invoice->id,
        );

        return Inertia::render('MyInvoiceDetail', $invoiceDetail);
    }

    public function checkout(Request $request)
    {
        $store = Store::with([
            'advantages',
            'certificates' => function ($query) {
                $query->limit(5);
            },
            'social_links',
        ])->first();

        $paymentMethods = PaymentMethod::get();
        $shippingMethods = ShippingMethod::get();

        return Inertia::render('Checkout', [
            'store' => $store,
            'paymentMethods' => $paymentMethods,
            'shippingMethods' => $shippingMethods,
        ]);
    }

    public function checkoutGuest(Request $request)
    {
        $store = Store::with([
            'advantages',
            'certificates' => function ($query) {
                $query->limit(5);
            },
            'social_links',
        ])->first();

        $paymentMethods = PaymentMethod::get();
        $shippingMethods = ShippingMethod::get();

        return Inertia::render('Checkout', [
            'store' => $store,
            'paymentMethods' => $paymentMethods,
            'shippingMethods' => $shippingMethods,
            'isGuest' => true,
        ]);
    }
}

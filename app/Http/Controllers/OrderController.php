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
            $transaction = Transaction::with(['payment_method', 'shipping_method', 'payments'])
                ->where('code', $transaction_code)
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
        } else {
            Log::error('No invoice or transaction code provided for order success.');
            return redirect()->route('my-order')->with('error', 'Invalid order details.');
        }

        // Decode midtrans response for each payment
        $transaction->payments = $transaction->payments->map(function ($payment) {
            $payment->midtrans_response = json_decode($payment->midtrans_response, true);
            return $payment;
        });

        return Inertia::render('OrderSuccess', [
            'transaction' => $transaction,
            'groups' => $groups,
        ]);
    }

    public function myOrder(Request $request)
    {
        $store = Store::with([
            'advantages',
            'certificates' => function ($query) {
                $query->limit(5);
            },
            'social_links',
        ])->first();

        $transactions = Transaction::with(['payment_method', 'shipping_method', 'items'])
            ->where('user_id', Auth::id())
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('MyOrder', [
            'transactions' => $transactions,
            'store' => $store,
        ]);
    }

    public function myOrderDetail(Request $request, $transaction_code)
    {
        $store = Store::with([
            'advantages',
            'certificates' => function ($query) {
                $query->limit(5);
            },
            'social_links',
        ])->first();

        $transaction = Transaction::with([
            'payment_method',
            'shipping_method',
            'payments' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
        ])
            ->where('code', $transaction_code)
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

        return Inertia::render('MyOrderDetail', [
            'transaction' => $transaction,
            'groups' => $groups,
            'store' => $store,
        ]);
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
}

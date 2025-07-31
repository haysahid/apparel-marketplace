<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\RajaongkirRepository;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Repositories\MidtransRepository;
use App\Repositories\VoucherRepository;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $rajaongkirRepository;
    protected $midtransRepository;

    protected $weight = 1000; // 1000 gram (1 kg)
    protected $courier = 'jne'; // Courier service

    public function __construct()
    {
        $this->rajaongkirRepository = new RajaongkirRepository();
        $this->midtransRepository = new MidtransRepository();
    }

    public function getVouchers(Request $request)
    {
        $storeId = $request->input('store_id');

        $vouchers = VoucherRepository::getAllVouchers($storeId);
        return ResponseFormatter::success($vouchers);
    }

    public function checkVoucher(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $voucher = VoucherRepository::getVoucherByCode(
            code: $validated['code'],
            storeId: $validated['store_id'] ?? null
        );

        if (!$voucher) {
            return ResponseFormatter::error(
                message: 'Voucher diskon tidak ditemukan atau sudah tidak berlaku.',
                code: 404
            );
        }

        return ResponseFormatter::success(
            $voucher,
            'Voucher diskon berhasil ditemukan.'
        );
    }

    public function syncCart(Request $request)
    {
        $validated = $request->validate([
            'cart_groups' => 'required|array',
            'cart_groups.*.store_id' => 'required|integer|exists:stores,id',
            'cart_groups.*.created_at' => 'nullable|string',
            'cart_groups.*.updated_at' => 'nullable|string',
            'cart_groups.*.items' => 'nullable|array',
            'cart_groups.*.items.*.store_id' => 'required|integer|exists:stores,id',
            'cart_groups.*.items.*.product_id' => 'required|integer',
            'cart_groups.*.items.*.variant_id' => 'required|integer',
            'cart_groups.*.items.*.quantity' => 'required|integer|min:1',
            'cart_groups.*.items.*.image' => 'nullable|string',
            'cart_groups.*.items.*.created_at' => 'nullable|string',
            'cart_groups.*.items.*.updated_at' => 'nullable|string',
            'cart_groups.*.items.*.selected' => 'nullable|boolean',
        ], [
            'cart_groups.required' => 'Keranjang harus diisi',
            'cart_groups.*.store_id.required' => 'ID toko harus diisi',
            'cart_groups.*.store_id.integer' => 'ID toko harus berupa angka',
            'cart_groups.*.store_id.exists' => 'Toko tidak ditemukan',
            'cart_groups.*.created_at.string' => 'Created at harus berupa string',
            'cart_groups.*.updated_at.string' => 'Updated at harus berupa string',
            'cart_groups.*.items.*.store_id.required' => 'ID toko harus diisi',
            'cart_groups.*.items.*.store_id.integer' => 'ID toko harus berupa angka',
            'cart_groups.*.items.*.store_id.exists' => 'Toko tidak ditemukan',
            'cart_groups.*.items.*.product_id.required' => 'ID produk harus diisi',
            'cart_groups.*.items.*.product_id.integer' => 'ID produk harus berupa angka',
            'cart_groups.*.items.*.variant_id.required' => 'ID varian produk harus diisi',
            'cart_groups.*.items.*.variant_id.integer' => 'ID varian produk harus berupa angka',
            'cart_groups.*.items.*.quantity.required' => 'Jumlah produk harus diisi',
            'cart_groups.*.items.*.quantity.integer' => 'Jumlah produk harus berupa angka',
            'cart_groups.*.items.*.quantity.min' => 'Jumlah produk minimal 1',
            'cart_groups.*.items.*.image.string' => 'Gambar harus berupa string',
            'cart_groups.*.items.*.created_at.string' => 'Created at harus berupa string',
            'cart_groups.*.items.*.updated_at.string' => 'Updated at harus berupa string',
            'cart_groups.*.items.*.selected.boolean' => 'Selected harus berupa boolean',
        ]);

        try {
            $cartGroups = $validated['cart_groups'];
            $updateCartGroups = [];

            foreach ($cartGroups as $key => $group) {
                // Skip empty groups
                if (empty($group['items'])) continue;

                $updatedGroup = $group;

                $store = Store::find($group['store_id']);
                $updatedGroup['store'] = $store;
                $updatedGroup['updated_at'] = now()->toDateTimeString();

                $items = $group['items'];
                $updateItems = [];

                foreach ($items as $item) {
                    $product = Product::find($item['product_id']);
                    if (!$product) continue;

                    $variant = ProductVariant::find($item['variant_id']);
                    if (!$variant) continue;

                    $updatedItem = $item;

                    // Add variant and image to the item
                    $updatedItem['variant'] = $variant;
                    $updatedItem['image'] = $variant->images->first()->image ?? $product->images->first()->image;

                    // Add created_at and updated_at timestamps
                    $updatedItem['updated_at'] = $item['updated_at'] ?? now()->toDateTimeString();

                    $updateItems[] = $updatedItem;
                }

                $updatedGroup['items'] = $updateItems;
                $updateCartGroups[$key] = $updatedGroup;
            }

            return ResponseFormatter::success(
                $updateCartGroups,
                'Keranjang berhasil disinkronisasi'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                'Gagal menyinkronkan keranjang',
                500
            );
        }
    }

    // Deprecated code, kept for reference
    public function provinces(Request $request)
    {
        try {
            $provinces = [
                ['province_id' => '11', 'province_name' => 'Jakarta'],

            ];

            $cacheKey = 'rajaongkir_provinces';
            $provinces = cache()->remember($cacheKey, 60 * 60, function () {
                $client = new Client();
                $response = $client->get('https://api-sandbox.collaborator.komerce.id/starter/province', [
                    'headers' => [
                        'key' => env('RAJAONGKIR_API_KEY'),
                    ],
                ]);
                return json_decode($response->getBody()->getContents())->rajaongkir->results;
            });

            return ResponseFormatter::success(
                $provinces,
                'Data retrieved successfully'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                'Failed to retrieve data',
                500
            );
        }
    }

    // Deprecated code, kept for reference
    public function cities(Request $request)
    {
        try {
            $provinceId = $request->input('province_id');
            $cacheKey = 'rajaongkir_cities_' . $provinceId;
            $cities = cache()->remember($cacheKey, 60 * 60, function () use ($provinceId) {
                $client = new Client();
                $response = $client->get('https://api-sandbox.collaborator.komerce.id/starter/city', [
                    'query' => ['province' => $provinceId],
                    'headers' => [
                        'key' => env('RAJAONGKIR_API_KEY'),
                    ],
                ]);
                return json_decode($response->getBody()->getContents())->rajaongkir->results;
            });

            return ResponseFormatter::success(
                $cities,
                'Data retrieved successfully'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                'Failed to retrieve data',
                500
            );
        }
    }

    public function destinations(Request $request)
    {
        $validated = $request->validate([
            'search' => 'required|string|max:255',
        ]);

        try {
            $search = $validated['search'];
            $destinations = $this->rajaongkirRepository->getDestinations($search);

            return ResponseFormatter::success(
                $destinations,
                'Data alamat tujuan berhasil diambil'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                'Gagal mengambil data alamat tujuan' . $e->getMessage(),
                500
            );
        }
    }

    public function shippingCost(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'required|integer',
            'store_ids' => 'required|string',
        ], [
            'destination.required' => 'ID tujuan harus diisi',
            'destination.integer' => 'ID tujuan harus berupa angka',
            'store_ids.required' => 'ID toko harus diisi',
            'store_ids.string' => 'ID toko harus berupa string',
        ]);

        try {
            $storeIds = explode(',', $validated['store_ids']);
            $stores = Store::whereIn('id', $storeIds)->get();

            if ($stores->isEmpty()) {
                return ResponseFormatter::error(
                    'Tidak ada toko yang ditemukan',
                    404
                );
            }

            $weight = $this->weight;
            $courier = $this->courier;
            $destinationId = $request->input('destination');

            $shippings = [];

            foreach ($stores as $store) {
                $originId = $store->rajaongkir_origin_id;

                // Get shipping cost
                $shipping = $this->rajaongkirRepository->getShipping($originId, $destinationId, $weight, $courier);
                if ($shipping) {
                    $shippings[] = [
                        'store_id' => $store->id,
                        'shipping' => $shipping,
                    ];
                }
            }

            if (empty($shippings)) {
                return ResponseFormatter::error(
                    'Tidak ada biaya pengiriman yang ditemukan',
                    404
                );
            }

            return ResponseFormatter::success(
                $shippings,
                'Biaya pengiriman berhasil diambil'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                'Gagal mengambil biaya pengiriman: ' . $e->getMessage(),
                500
            );
        }
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'cart_groups' => 'required|array',
            'cart_groups.*.store_id' => 'required|integer|exists:stores,id',
            'cart_groups.*.voucher_code' => 'nullable|string|exists:vouchers,code',
            'cart_groups.*.items' => 'required|array',
            'cart_groups.*.items.*.product_id' => 'required|integer|exists:products,id',
            'cart_groups.*.items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'cart_groups.*.items.*.quantity' => 'required|integer|min:1',
            'payment_method_id' => 'required|integer|exists:payment_methods,id',
            'shipping_method_id' => 'required|integer|exists:shipping_methods,id',
            'destination_id' => 'nullable|integer',
            'destination_label' => 'nullable|string',
            'province_name' => 'nullable|string',
            'city_name' => 'nullable|string',
            'district_name' => 'nullable|string',
            'subdistrict_name' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'address' => 'nullable|string',
            'note' => 'nullable|string',
            'voucher_code' => 'nullable|string|exists:vouchers,code',
        ], [
            'cart_groups.required' => 'Keranjang harus diisi',
            'cart_groups.*.store_id.required' => 'ID toko harus diisi',
            'cart_groups.*.store_id.exists' => 'Toko tidak ditemukan',
            'cart_groups.*.voucher_code.string' => 'Kode voucher harus berupa string',
            'cart_groups.*.voucher_code.exists' => 'Kode voucher tidak ditemukan',
            'cart_groups.*.items.required' => 'Item keranjang harus diisi',
            'cart_groups.*.items.*.product_id.required' => 'ID produk harus diisi',
            'cart_groups.*.items.*.product_id.exists' => 'Produk tidak ditemukan',
            'cart_groups.*.items.*.variant_id.required' => 'ID varian produk harus diisi',
            'cart_groups.*.items.*.variant_id.exists' => 'Varian produk tidak ditemukan',
            'cart_groups.*.items.*.quantity.min' => 'Jumlah produk minimal 1',
            'payment_method_id.required' => 'Metode pembayaran harus diisi',
            'payment_method_id.exists' => 'Metode pembayaran tidak ditemukan',
            'shipping_method_id.required' => 'Metode pengiriman harus diisi',
            'shipping_method_id.exists' => 'Metode pengiriman tidak ditemukan',
            'destination_id.integer' => 'ID tujuan harus berupa angka',
            'destination_label.string' => 'Label tujuan harus berupa string',
            'province_name.string' => 'Nama provinsi harus berupa string',
            'city_name.string' => 'Nama kota harus berupa string',
            'district_name.string' => 'Nama kecamatan harus berupa string',
            'subdistrict_name.string' => 'Nama kelurahan harus berupa string',
            'zip_code.string' => 'Kode pos harus berupa string',
            'address.string' => 'Alamat harus berupa string',
            'note.string' => 'Catatan harus berupa string',
            'voucher_code.string' => 'Kode voucher harus berupa string',
            'voucher_code.exists' => 'Kode voucher tidak ditemukan',
        ]);

        try {
            DB::beginTransaction();

            // Get transaction voucher if provided
            $transactionVoucher = null;
            if (isset($validated['voucher_code'])) {
                $transactionVoucher = VoucherRepository::getVoucherByCode($validated['voucher_code']);
                if (!$transactionVoucher) {
                    DB::rollBack();
                    return ResponseFormatter::error(
                        'Voucher tidak ditemukan',
                        404
                    );
                }
            }

            $paymentMethod = PaymentMethod::findOrFail($validated['payment_method_id']);
            $shippingMethod = ShippingMethod::findOrFail($validated['shipping_method_id']);

            $transaction = null;
            $totalPayment = 0;
            $itemDetails = [];
            $invoices = [];

            // [START] Processing each cart group
            foreach ($validated['cart_groups'] as $key => $group) {
                $store = Store::findOrFail($group['store_id']);
                $originId = $store->rajaongkir_origin_id;

                $cartItems = $group['items'];

                if ($shippingMethod->slug === 'courier') {
                    $rajaongkirShipping = $request->validate([
                        'destination_id' => 'required|integer',
                        'address' => 'required|string',
                    ], [
                        'destination_id.required' => 'ID tujuan harus diisi',
                        'destination_id.integer' => 'ID tujuan harus berupa angka',
                        'address.required' => 'Alamat harus diisi',
                        'address.string' => 'Alamat harus berupa string',
                    ]);

                    // Get shipping cost
                    $weight = $this->weight;
                    $courier = $this->courier;
                    $destinationId = $rajaongkirShipping['destination_id'];
                    $shipping = $this->rajaongkirRepository->getShipping($originId, $destinationId, $weight, $courier);

                    if (!$shipping) {
                        throw new Exception('Gagal mendapatkan biaya pengiriman');
                    }

                    $rajaongkirDestinationId = $validated['destination_id'] ?? null;
                    $rajaongkirDestinationLabel = $validated['destination_label'] ?? null;
                    $provinceName = $validated['province_name'] ?? null;
                    $cityName = $validated['city_name'] ?? null;
                    $districtName = $validated['district_name'] ?? null;
                    $subdistrictName = $validated['subdistrict_name'] ?? null;
                    $zipCode = $validated['zip_code'] ?? null;
                    $address = $validated['address'] ?? null;
                    $shippingCost = $shipping['cost'] ?? 0;
                    $shippingEstimate = $shipping['etd'] ?? null;
                } else {
                    // For non-courier shipping methods, set default values
                    $rajaongkirDestinationId = null;
                    $rajaongkirDestinationLabel = null;
                    $provinceName = null;
                    $cityName = null;
                    $districtName = null;
                    $subdistrictName = null;
                    $zipCode = null;
                    $address = null;
                    $shippingCost = 0;
                    $shippingEstimate = null;
                }

                // Create transaction
                if (!$transaction) {
                    $transaction = Transaction::create([
                        'user_id' => Auth::id(),
                        'type_id' => 2, // sale
                        'code' => 'SL-' . date('YmdHis'),
                        'note' => $validated['note'] ?? null,
                        'payment_method_id' => $validated['payment_method_id'],
                        'shipping_method_id' => $validated['shipping_method_id'],
                        'rajaongkir_destination_id' => $rajaongkirDestinationId,
                        'rajaongkir_destination_label' => $rajaongkirDestinationLabel,
                        'province_name' => $provinceName,
                        'city_name' => $cityName,
                        'district_name' => $districtName,
                        'subdistrict_name' => $subdistrictName,
                        'zip_code' => $zipCode,
                        'address' => $address,
                        'shipping_cost' => 0,
                        'shipping_estimate' => $shippingEstimate,
                        'status' => 'pending',
                    ]);
                }

                // Create transaction items
                $subTotal = 0;
                foreach ($cartItems as $item) {
                    $variant = ProductVariant::findOrFail($item['variant_id']);
                    $itemSubTotal = $item['quantity'] * $variant->final_selling_price;
                    $subTotal += $itemSubTotal;

                    TransactionItem::create([
                        'store_id' => $store->id,
                        'transaction_id' => $transaction->id,
                        'variant_id' => $variant->id,
                        'quantity' => $item['quantity'],
                        'unit_base_price' => $variant->base_selling_price,
                        'unit_discount_type' => $variant->discount_type,
                        'unit_discount' => $variant->discount,
                        'unit_final_price' => $variant->final_selling_price,
                        'subtotal' => $itemSubTotal,
                        'fullfillment_status' => 'pending',
                    ]);
                }

                // Calculate total
                $baseTotal = $subTotal;
                $total = $subTotal + $shippingCost;

                // Create invoice
                if ($paymentMethod->slug === 'transfer') {
                    $itemDetails = TransactionItem::with(
                        [
                            'variant.product' => function ($query) {
                                $query->with('brand', 'categories');
                            },
                            'store' => function ($query) {
                                $query->select('id', 'name');
                            },
                        ]
                    )->where('transaction_id', $transaction->id)->get()->map(function ($item) {
                        return [
                            'id' => $item->variant_id,
                            'price' => $item->unit_final_price,
                            'quantity' => $item->quantity,
                            'name' => $item->variant->sku,
                            'brand' => $item->variant->product->brand->name ?? null,
                            'merchant_name' => $item->store->name,
                            'url' => $item->variant->product->url,
                        ];
                    })->toArray();

                    $invoice = Invoice::create([
                        'store_id' => $store->id,
                        'transaction_id' => $transaction->id,
                        'code' => 'INV-' . date('YmdHis') . '-' . $key,
                        'base_amount' => $baseTotal,
                        'shipping_cost' => $shippingCost,
                        'shipping_estimate' => $shippingEstimate,
                        'tax' => 0,
                        'amount' => $total,
                        'due_date' => now()->addDays(1),
                        'snap_token' => null,
                    ])->load('store');
                } else {
                    $invoice = Invoice::create([
                        'store_id' => $store->id,
                        'transaction_id' => $transaction->id,
                        'code' => 'INV-' . date('YmdHis') . '-' . $key,
                        'base_amount' => $baseTotal,
                        'shipping_cost' => 0,
                        'tax' => 0,
                        'amount' => $total,
                        'due_date' => now()->addDays(1),
                    ])->load('store');
                }

                // Get store voucher if provided
                $storeVoucher = null;
                if (isset($group['voucher_code'])) {
                    $storeVoucher = VoucherRepository::getVoucherByCode(
                        code: $group['voucher_code'],
                        storeId: $store->id,
                    );

                    if (!$storeVoucher) {
                        DB::rollBack();
                        return ResponseFormatter::error(
                            'Voucher tidak ditemukan',
                            404
                        );
                    }
                }

                if ($storeVoucher) {
                    // Apply voucher discount
                    $discountAmount = VoucherRepository::calculateVoucherAmount(
                        voucher: $storeVoucher,
                        amount: $invoice->base_amount,
                    );

                    // Update invoice with discount
                    $invoice->voucher_id = $storeVoucher->id;
                    $invoice->voucher_amount = $discountAmount;
                    $invoice->amount = $invoice->amount - $discountAmount;
                    $invoice->save();
                    $invoice->load('voucher');

                    // Update transaction total payment
                    $totalPayment = $totalPayment + $invoice->amount;

                    $itemDetails[] = [
                        'id' => $storeVoucher->id,
                        'price' => -$discountAmount,
                        'quantity' => 1,
                        'name' => 'Diskon - ' . $storeVoucher->code,
                        'brand' => null,
                        'merchant_name' => $store->name,
                        'url' => null,
                    ];
                }

                $invoices[] = $invoice;

                // Update transaction with shipping cost
                $transaction->shipping_cost = $transaction->shipping_cost + $shippingCost;
                $transaction->save();

                if ($shippingMethod->slug === 'courier') {
                    $itemDetails[] = [
                        'id' => 'shipping',
                        'price' => $shippingCost,
                        'quantity' => 1,
                        'name' => 'Biaya Pengiriman',
                        'brand' => null,
                        'merchant_name' => null,
                        'url' => null,
                    ];
                }
            }
            // [END] Processing each cart group

            if ($transactionVoucher) {
                // Apply transaction voucher discount
                $discountAmount = VoucherRepository::calculateVoucherAmount(
                    voucher: $transactionVoucher,
                    amount: $totalPayment - $transaction->shipping_cost,
                );

                $totalPayment = $totalPayment - $discountAmount;

                // Update transaction with discount
                $transaction->voucher_id = $transactionVoucher->id;
                $transaction->voucher_amount = $discountAmount;
                $transaction->save();
                $transaction->load('voucher');
            }

            // Create payment record
            $customer = User::find(Auth::id());
            $grossAmount = $totalPayment;

            if ($paymentMethod->slug === 'transfer') {
                // Create snap token for Midtrans
                $snapToken = $this->midtransRepository->createSnapToken(
                    $transaction->code,
                    $itemDetails,
                    $customer,
                    $grossAmount
                );

                if (!$snapToken) {
                    throw new Exception('Gagal mendapatkan token Snap');
                }

                // Create payment record
                $payment = Payment::create([
                    'transaction_id' => $transaction->id,
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => $grossAmount,
                    'note' => $validated['note'] ?? null,
                    'status' => 'pending',
                    'midtrans_snap_token' => $snapToken,
                ]);
            } else {
                $payment = Payment::create([
                    'transaction_id' => $transaction->id,
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => $grossAmount,
                    'note' => $validated['note'] ?? null,
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            return ResponseFormatter::success(
                [
                    'transaction' => $transaction,
                    'invoices' => $invoices,
                    'payment' => $payment,
                ],
                'Pesanan berhasil dibuat',
                201
            );
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Checkout failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            return ResponseFormatter::error(
                'Checkout failed' . $e->getMessage(),
                500
            );
        }
    }

    public function cancelOrder(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($validated['invoice_id']);
            $transaction = Transaction::findOrFail($invoice->transaction_id);

            // Check if transaction is already paid
            if ($transaction->status === 'paid') {
                return ResponseFormatter::error(
                    'Transaksi sudah dibayar, tidak dapat dibatalkan',
                    400
                );
            }

            // Update transaction status to cancelled
            $transaction->status = 'cancelled';
            $transaction->save();

            DB::commit();

            return ResponseFormatter::success(
                null,
                'Pesanan berhasil dibatalkan',
                200
            );
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Cancel order failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'invoice_id' => $validated['invoice_id'],
            ]);

            return ResponseFormatter::error(
                'Gagal membatalkan pesanan: ' . $e->getMessage(),
                500
            );
        }
    }

    public function checkPayment(Request $request)
    {
        $validated = $request->validate([
            'transaction_code' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::with(['payments'])->where('code', $validated['transaction_code'])->first();
            $payment = Payment::where('transaction_id', $transaction->id)->first();

            if (!$payment) {
                return ResponseFormatter::error(
                    'Pembayaran tidak ditemukan',
                    404
                );
            }

            $transactionCode = $transaction->code;

            if ($transaction->payments->count() > 1) {
                $transactionCode = $transaction->code . '-' . ($transaction->payments->count() - 1);
            }

            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
            \Midtrans\Config::$is3ds = true;

            $response = (object) \Midtrans\Transaction::status($transactionCode);

            $paymentStatusBefore = $payment->status;

            // Update payment
            $payment->midtrans_response = json_encode($response);
            $paymentStatusAfter = $response->transaction_status == 'settlement'
                ? 'completed'
                : ($response->transaction_status == 'failed' ? 'failed' : 'pending');

            // Update invoice paid_at if payment is completed
            if ($paymentStatusAfter === 'completed' && ($paymentStatusBefore !== 'completed' || $transaction->status === 'pending')) {
                // Update payment status
                $payment->status = $paymentStatusAfter;
                $payment->save();

                // Update invoice paid_at
                Invoice::where('transaction_id', $transaction->id)
                    ->update(['paid_at' => now()]);
                $transaction->paid_at = now();
                $transaction->status = 'paid';
                $transaction->save();

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
            }

            DB::commit();

            $payment->midtrans_response = json_decode($payment->midtrans_response, true);

            return ResponseFormatter::success(
                $payment,
                'Status pembayaran berhasil diperiksa',
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Check payment failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'transaction_code' => $validated['transaction_code'],
            ]);

            return ResponseFormatter::error(
                'Gagal memeriksa status pembayaran: ' . $e->getMessage(),
                500
            );
        }
    }

    public function changePaymentType(Request $request)
    {
        $validated = $request->validate([
            'transaction_code' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::with([
                'payment_method',
                'shipping_method',
                'invoices',
                'payments'
            ])->where('code', $validated['transaction_code'])->first();

            if (!$transaction) {
                return ResponseFormatter::error(
                    'Transaksi tidak ditemukan',
                    404
                );
            }

            $itemDetails = TransactionItem::with(
                [
                    'variant.product' => function ($query) {
                        $query->with('brand', 'categories');
                    },
                    'store' => function ($query) {
                        $query->select('id', 'name');
                    },
                ]
            )->whereHas('transaction', function ($query) use ($transaction) {
                $query->where('code', $transaction->code);
            })->get()->map(function ($item) {
                return [
                    'id' => $item->variant_id,
                    'price' => $item->unit_final_price,
                    'quantity' => $item->quantity,
                    'name' => $item->variant->sku,
                    'brand' => $item->variant->product->brand->name ?? null,
                    'merchant_name' => $item->store->name,
                    'url' => $item->variant->product->url,
                ];
            })->toArray();

            if ($transaction->shipping_method->slug === 'courier') {
                $shippingCost = $transaction->shipping_cost;
                $itemDetails[] = [
                    'id' => 'shipping',
                    'price' => $shippingCost,
                    'quantity' => 1,
                    'name' => 'Biaya Pengiriman',
                    'brand' => null,
                    'merchant_name' => null,
                    'url' => null,
                ];
            }

            $customer = User::find(Auth::id());
            $grossAmount = $transaction->invoices->sum('amount');

            $paymentsLength = $transaction->payments->count();

            $snapToken = $this->midtransRepository->createSnapToken(
                $transaction->code . '-' . $paymentsLength,
                $itemDetails,
                $customer,
                $grossAmount
            );

            if (!$snapToken) {
                throw new Exception('Gagal mendapatkan token Snap');
            }

            // Create new payment record
            $payment = Payment::create([
                'transaction_id' => $transaction->id,
                'payment_method_id' => $transaction->payment_method->id,
                'amount' => $grossAmount,
                'note' => null,
                'status' => 'pending',
                'midtrans_snap_token' => $snapToken,
            ]);

            DB::commit();

            return ResponseFormatter::success(
                $payment,
                'Token Snap berhasil diperoleh',
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Check payment failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'transaction_code' => $validated['transaction_code'],
            ]);

            return ResponseFormatter::error(
                'Gagal mengganti jenis pembayaran: ' . $e,
                500
            );
        }
    }

    public function confirmPayment(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|integer|exists:payments,id',
        ]);

        try {
            DB::beginTransaction();

            $payment = Payment::with(['transaction.payment_method'])->findOrFail($validated['payment_id']);
            $transaction = $payment->transaction;

            // Check midtrans payment status
            if ($transaction->payment_method->slug === 'transfer') {
                \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
                \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
                \Midtrans\Config::$is3ds = true;

                $response = (object) \Midtrans\Transaction::status($transaction->code);

                if ($response->transaction_status !== 'settlement') {
                    throw new Exception('Pembayaran belum terkonfirmasi');
                }

                // Update payment status
                $payment->midtrans_response = json_encode($response);
                $payment->status = 'completed';
                $payment->save();

                // Update invoice paid_at
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
            }

            DB::commit();

            return ResponseFormatter::success(
                null,
                'Pembayaran berhasil dikonfirmasi',
                200
            );
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Confirm payment failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return ResponseFormatter::error(
                'Gagal mengonfirmasi pembayaran: ' . $e->getMessage(),
                500
            );
        }
    }

    public function changeStatus(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
            'status' => 'required|string|in:pending,paid,processing,completed,cancelled',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($validated['invoice_id']);
            $invoice->status = $validated['status'];
            $invoice->save();

            DB::commit();

            return ResponseFormatter::success(
                $invoice,
                'Status transaksi berhasil diubah',
                200
            );
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Change status failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'transaction_id' => $validated['transaction_id'],
            ]);

            return ResponseFormatter::error(
                'Gagal mengubah status transaksi: ' . $e->getMessage(),
                500
            );
        }
    }
}

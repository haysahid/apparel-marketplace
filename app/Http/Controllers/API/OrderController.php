<?php

namespace App\Http\Controllers\API;

use App\Core\DataFailure;
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
use App\UseCases\CheckoutUseCase;
use App\UseCases\SyncCartUseCase;
use App\UseCases\ValidateTransactionPaymentUseCase;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    private CheckoutUseCase $checkoutUseCase;
    private ValidateTransactionPaymentUseCase $validateTransactionPaymentUseCase;

    protected $rajaongkirRepository;
    protected $midtransRepository;

    protected $weight = 1000; // 1000 gram (1 kg)
    protected $courier = 'jne'; // Courier service

    public function __construct()
    {
        $this->checkoutUseCase = new CheckoutUseCase();
        $this->validateTransactionPaymentUseCase = new ValidateTransactionPaymentUseCase();
        $this->rajaongkirRepository = new RajaongkirRepository();
        $this->midtransRepository = new MidtransRepository();
    }

    public function getVouchers(Request $request)
    {
        $storeId = $request->input('store_id');
        $userId = $request->input('user_id');

        $vouchers = VoucherRepository::getAllVouchers(
            storeId: $storeId,
            userId: $userId ?? Auth::id(),
            isPublic: true,
        );
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

                $updateCartGroups[$key] = SyncCartUseCase::sync($group);
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

        return $this->checkoutUseCase->execute(
            data: $validated,
            isGuestCheckout: false,
        )->fold(
            onSuccess: fn($data, $code) => ResponseFormatter::success($data, 'Pesanan berhasil dibuat', $code),
            onError: fn($error, $code) => ResponseFormatter::error($error, $code)
        );
    }

    public function checkoutGuest(Request $request)
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
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'required|string|max:20',
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
            'guest_name.required' => 'Nama harus diisi',
            'guest_name.string' => 'Nama harus berupa string',
            'guest_name.max' => 'Nama maksimal 255 karakter',
            'guest_email.required' => 'Email harus diisi',
            'guest_email.email' => 'Email tidak valid',
            'guest_email.max' => 'Email maksimal 255 karakter',
            'guest_phone.required' => 'Nomor telepon harus diisi',
            'guest_phone.string' => 'Nomor telepon harus berupa string',
            'guest_phone.max' => 'Nomor telepon maksimal 20 karakter',
        ]);

        return $this->checkoutUseCase->execute(
            data: $validated,
            isGuestCheckout: true,
        )->fold(
            onSuccess: fn($data, $code) => ResponseFormatter::success($data, 'Pesanan berhasil dibuat', $code),
            onError: fn($error, $code) => ResponseFormatter::error($error, $code)
        );
    }

    public function checkoutStore(Request $request)
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
            'customer_id' => 'nullable|integer|exists:users,id',
            'guest_name' => 'nullable|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
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
            'customer_id.integer' => 'ID pelanggan harus berupa angka',
            'customer_id.exists' => 'Pelanggan tidak ditemukan',
            'guest_name.string' => 'Nama harus berupa string',
            'guest_name.max' => 'Nama maksimal 255 karakter',
            'guest_email.email' => 'Email tidak valid',
            'guest_email.max' => 'Email maksimal 255 karakter',
            'guest_phone.string' => 'Nomor telepon harus berupa string',
            'guest_phone.max' => 'Nomor telepon maksimal 20 karakter',
        ]);

        return $this->checkoutUseCase->execute(
            data: $validated,
            isStoreCheckout: true,
        )->fold(
            onSuccess: fn($data, $code) => ResponseFormatter::success($data, 'Pesanan berhasil dibuat', $code),
            onError: fn($error, $code) => ResponseFormatter::error($error, $code)
        );
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

        return $this->validateTransactionPaymentUseCase->validate($validated['transaction_code'])->fold(
            onSuccess: function ($data, $code) {
                return ResponseFormatter::success(
                    $data,
                    'Status pembayaran berhasil diperiksa',
                    $code
                );
            },
            onError: fn($error, $code) => ResponseFormatter::error($error, $code)
        );
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
                $this->validateTransactionPaymentUseCase->validate($transaction->code);
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

    public function midtransPaymentMethods(Request $request)
    {
        try {
            $paymentMethods = $this->midtransRepository->getPaymentMethods();

            return ResponseFormatter::success(
                $paymentMethods,
                'Metode pembayaran berhasil diambil',
                200
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                'Gagal mengambil metode pembayaran: ' . $e->getMessage(),
                500
            );
        }
    }
}

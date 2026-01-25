<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportRepository
{
    protected $reportQuery;
    protected $startDate;
    protected $endDate;
    protected $store;

    public function __construct()
    {
        $this->startDate = null;
        $this->endDate = now()->format('Y-m-d');

        $this->reportQuery = Transaction::query()
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->join('transaction_types', 'transactions.type_id', '=', 'transaction_types.id');

        $this->store = Store::first();
    }

    public function getPurchaseReport($startDate, $endDate)
    {
        $endDate = $this->endDate;

        if ($startDate) $this->startDate = "$startDate 00:00:00";
        if ($endDate) $this->endDate = "$endDate 23:59:59";

        $selects = [
            'transactions.created_at as date',
            'transactions.code',
            'users.name as customer',
            'transactions.status',
            'COUNT(transaction_items.id) as total_items',
            'CAST(SUM(transaction_items.unit_base_selling_price::numeric * transaction_items.quantity) AS INTEGER) as total_purchase',
            'CAST(SUM(transaction_items.subtotal) AS INTEGER) as total_sales',
            'CAST(SUM(transaction_items.unit_base_selling_price::numeric * transaction_items.quantity - transaction_items.subtotal) AS INTEGER) as total_discounts',
            'CAST(SUM(transaction_items.subtotal) - SUM(transaction_items.unit_base_selling_price::numeric * transaction_items.quantity) AS INTEGER) as margin'
        ];

        $query = $this->reportQuery
            ->selectRaw(implode(', ', $selects))
            ->where('transaction_types.slug', 'purchase');

        if ($this->startDate) {
            $query->whereDate('transactions.created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('transactions.created_at', '<=', $this->endDate);
        }

        $report = $query
            ->groupBy('transactions.id')
            ->get();

        $totals = [
            'total_items' => 0,
            'total_purchase' => 0,
            'total_sales' => 0,
            'total_discounts' => 0,
            'margin' => 0,
        ];

        foreach ($report as $item) {
            $totals['total_items'] += $item->total_items;
            $totals['total_purchase'] += $item->total_purchase;
            $totals['total_sales'] += $item->total_sales;
            $totals['total_discounts'] += $item->total_discounts;
            $totals['margin'] += $item->margin;
        }

        return [
            'title' => "Laporan Pembelian {$this->store->name}",
            'report_type' => 'purchase',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'printed_at' => now()->format('Y-m-d H:i:s'),
            'report' => $report,
            'totals' => $totals,
        ];
    }

    public function getSalesReport($startDate, $endDate)
    {
        if ($startDate) $this->startDate = "$startDate 00:00:00";
        if ($endDate) $this->endDate = "$endDate 23:59:59";

        $selects = [
            'transactions.created_at as date',
            'transactions.code',
            'users.name as customer',
            'transactions.status',
            'COUNT(transaction_items.id) as total_items',
            'CAST(SUM(transaction_items.unit_purchase_price::numeric * transaction_items.quantity) AS INTEGER) as total_purchase',
            'CAST(SUM(transaction_items.subtotal) AS INTEGER) as total_sales',
            'CAST(SUM(transaction_items.unit_base_selling_price::numeric * transaction_items.quantity - transaction_items.subtotal) AS INTEGER) as total_discounts',
            'CAST(SUM(transaction_items.subtotal) - SUM(transaction_items.unit_purchase_price::numeric * transaction_items.quantity) - SUM(transaction_items.unit_base_selling_price::numeric * transaction_items.quantity - transaction_items.subtotal) AS INTEGER) as margin',
        ];

        $query = $this->reportQuery
            ->selectRaw(implode(', ', $selects))
            ->where('transaction_types.slug', 'sale');

        if ($this->startDate) {
            $query->whereDate('transactions.created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('transactions.created_at', '<=', $this->endDate);
        }

        $report = $query
            ->groupBy('transactions.id', 'transactions.code', 'users.name', 'transactions.status')
            ->get();

        $totals = [
            'total_items' => 0,
            'total_purchase' => 0,
            'total_sales' => 0,
            'total_discounts' => 0,
            'margin' => 0,
        ];

        foreach ($report as $item) {
            $totals['total_items'] += $item->total_items;
            $totals['total_purchase'] += $item->total_purchase;
            $totals['total_sales'] += $item->total_sales;
            $totals['total_discounts'] += $item->total_discounts;
            $totals['margin'] += $item->margin;
        }

        return [
            'title' => "Laporan Penjualan {$this->store->name}",
            'report_type' => 'sale',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'printed_at' => now()->format('Y-m-d H:i:s'),
            'report' => $report,
            'totals' => $totals,
        ];
    }

    public function getStockReport($brand)
    {
        $report = DB::table('product_variants')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('colors', 'product_variants.color_id', '=', 'colors.id')
            ->join('sizes', 'product_variants.size_id', '=', 'sizes.id')
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'brands.name as brand_name',
                'product_variants.sku',
                'product_variants.motif',
                'colors.name as color_name',
                'sizes.name as size_name',
                'product_variants.current_stock_level',
            )
            ->when($brand, function ($query) use ($brand) {
                return $query->where('brands.name', $brand);
            })
            ->groupBy('product_variants.id', 'products.id', 'brands.name', 'colors.name', 'sizes.name')
            ->orderBy('products.name', 'asc')
            ->get();

        $totals = [
            'total_stock' => 0,
        ];

        foreach ($report as $item) {
            $totals['total_stock'] += $item->current_stock_level;
        }

        Log::info('Stock Report Data', [
            'brand' => $brand,
            'total_stock' => $totals['total_stock'],
            'report_count' => count($report),
        ]);

        return [
            'title' => "Laporan Stok {$this->store->name}",
            'report_type' => 'stock',
            'brand' => $brand,
            'printed_at' => now()->format('Y-m-d H:i:s'),
            'report' => $report,
            'totals' => $totals,
        ];
    }
}

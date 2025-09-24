<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TransactionType::insert([
            [
                'name' => 'Pembelian',
                'slug' => 'purchase',
                'code_prefix' => 'PR',
                'description' => 'Transaksi pembelian barang.',
                'effect_on_stock' => 'inbound',
            ],
            [
                'name' => 'Penjualan',
                'slug' => 'sale',
                'code_prefix' => 'SL',
                'description' => 'Transaksi penjualan barang ke pelanggan.',
                'effect_on_stock' => 'outbound',
            ],
            [
                'name' => 'Retur Supplier',
                'slug' => 'return-to-supplier',
                'code_prefix' => 'RS',
                'description' => 'Transaksi pengembalian barang ke supplier.',
                'effect_on_stock' => 'outbound',
            ],
            [
                'name' => 'Retur',
                'slug' => 'return-from-customer',
                'code_prefix' => 'RC',
                'description' => 'Transaksi pengembalian barang dari pelanggan.',
                'effect_on_stock' => 'inbound',
            ],
            [
                'name' => 'Barang Hilang',
                'slug' => 'damaged-out',
                'code_prefix' => 'DO',
                'description' => 'Transaksi kehilangan atau kerusakan barang.',
                'effect_on_stock' => 'outbound',
            ],
            [
                'name' => 'Penggunaan Internal',
                'slug' => 'internal-use',
                'code_prefix' => 'IU',
                'description' => 'Transaksi penggunaan barang untuk keperluan internal.',
                'effect_on_stock' => 'outbound',
            ],
            [
                'name' => 'Penyesuaian Stok Masuk',
                'slug' => 'adjustment-in',
                'code_prefix' => 'AI',
                'description' => 'Transaksi penyesuaian stok masuk.',
                'effect_on_stock' => 'inbound',
            ],
            [
                'name' => 'Penyesuaian Stok Keluar',
                'slug' => 'adjustment-out',
                'code_prefix' => 'AO',
                'description' => 'Transaksi penyesuaian stok keluar.',
                'effect_on_stock' => 'outbound',
            ],
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\PointRule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PointRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PointRule::insert([
            [
                'store_id' => 1,
                'name' => 'Pembelian Produk',
                'description' => 'Dapatkan 10 poin untuk setiap pembelian di toko kami minimal Rp 100.000. Poin dapat ditukarkan dengan voucher diskon di pembelian berikutnya.',
                'type' => 'per_transaction',
                'min_spend' => 100000,
                'points_earned' => 10,
                'conversion_rate' => null,
                'valid_from' => now(),
                'valid_until' => null,
                'disabled_at' => null,
                'created_at' => now(),
            ],
            [
                'store_id' => 1,
                'name' => 'Ulasan Produk',
                'description' => 'Dapatkan 5 poin untuk setiap ulasan produk yang Anda berikan di toko kami. Poin dapat ditukarkan dengan voucher diskon di pembelian berikutnya.',
                'type' => 'on_review',
                'min_spend' => null,
                'points_earned' => 5,
                'conversion_rate' => null,
                'valid_from' => now(),
                'valid_until' => null,
                'disabled_at' => null,
                'created_at' => now(),
            ],
            [
                'store_id' => 1,
                'name' => 'Pendaftaran Akun',
                'description' => 'Dapatkan 20 poin saat Anda mendaftar akun di toko kami. Poin dapat ditukarkan dengan voucher diskon di pembelian berikutnya.',
                'type' => 'on_signup',
                'min_spend' => null,
                'points_earned' => 20,
                'conversion_rate' => null,
                'valid_from' => now(),
                'valid_until' => null,
                'disabled_at' => null,
                'created_at' => now(),
            ],
        ]);
    }
}

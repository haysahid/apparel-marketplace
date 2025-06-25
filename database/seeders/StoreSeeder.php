<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\StoreAdvantage;
use App\Models\StoreCertificate;
use App\Models\StoreSocialLink;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Store::updateOrCreate([
            'name' => 'Mustika Collection',
            'description' => 'Toko Mustika Collection merupakan toko distributor busana muslim yang berlokasi di Jl. Anggur Raya, Kutabumi, Kec. Ps. Kemis, Kabupaten Tangerang, Provinsi Banten. Menyediakan berbagai busana muslim lengkap untuk semua usia.',
            'address' => 'Jl. Anggur Raya, Kutabumi, Kec. Ps. Kemis, Kabupaten Tangerang, Provinsi Banten',
            'email' => 'store@example.com',
            'phone' => '021-12345678',
            'logo' => 'logo_black.png',
            'banner' => 'store.png',
        ]);

        StoreAdvantage::insert([
            [
                'store_id' => 1,
                'name' => 'Koleksi Lengkap',
                'description' => 'Kami menyediakan berbagai koleksi busana muslim untuk pria, wanita, dan anak-anak.',
            ],
            [
                'store_id' => 1,
                'name' => 'Kualitas Terjamin',
                'description' => 'Semua produk kami dibuat dengan bahan berkualitas tinggi dan proses yang teliti.',
            ],
            [
                'store_id' => 1,
                'name' => 'Pelayanan Ramah',
                'description' => 'Tim kami siap membantu Anda dengan pelayanan yang ramah dan profesional.',
            ],

            [
                'store_id' => 1,
                'name' => 'Pengiriman Cepat',
                'description' => 'Kami menyediakan layanan pengiriman cepat untuk memastikan produk sampai ke tangan Anda dengan segera.',
            ],
        ]);

        StoreCertificate::insert([
            [
                'store_id' => 1,
                'name' => 'Distributor Resmi',
                'description' => 'Kami adalah distributor resmi busana muslim terkemuka di Indonesia.',
                'image' => 'certificate.png',
            ],
            [
                'store_id' => 1,
                'name' => 'Sertifikat Kualitas',
                'description' => 'Kami memiliki sertifikasi kualitas untuk memastikan standar produk yang tinggi.',
                'image' => 'certificate.png',
            ],
        ]);

        StoreSocialLink::insert([
            [
                'store_id' => 1,
                'name' => 'Instagram',
                'url' => 'https://www.instagram.com/mustika.gamis',
                'icon' => 'icon/ic_instagram.svg',
            ],
            [
                'store_id' => 1,
                'name' => 'Facebook',
                'url' => 'https://facebook.com/nhs.pasarkemis1',
                'icon' => 'icon/ic_facebook.svg',
            ],
            [
                'store_id' => 1,
                'name' => 'TikTok',
                'url' => 'https://www.tiktok.com/@nhspasarkemis1',
                'icon' => 'icon/ic_tiktok.svg',
            ],
        ]);
    }
}

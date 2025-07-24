<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div
        data-aos="fade-up"
        data-aos-duration="600"
        class="w-full max-w-7xl mx-auto rounded-xl bg-[url(/storage/join_banner.png)] bg-secondary-box bg-cover bg-center bg-no-repeat h-[612px] flex items-center justify-center"
    >
        <div
            class="text-center max-w-[400px] flex flex-col items-center gap-6 px-4"
        >
            <h1 class="text-4xl font-bold sm:text-5xl">
                Dapatkan Banyak Diskon!
            </h1>
            <p class="text-gray-700">
                Bergabunglah dengan komunitas kami dan nikmati berbagai
                keuntungan sebagai anggota. Dapatkan akses eksklusif ke produk
                terbaru, diskon khusus, dan banyak lagi!
            </p>
            <div class="flex items-center justify-center gap-4">
                <Link
                    :href="route('login')"
                    class="px-6 py-3 font-semibold text-white transition duration-300 rounded-md bg-primary hover:bg-primary/90"
                >
                    Masuk
                </Link>
                <Link
                    :href="route('register')"
                    class="px-6 py-3 font-semibold text-gray-500 transition duration-300 bg-gray-300 rounded-md hover:bg-gray-300/80"
                >
                    Daftar
                </Link>
            </div>
        </div>
    </div>
</template>

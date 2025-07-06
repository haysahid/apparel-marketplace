<script setup lang="ts">
import LandingLayout from "@/Layouts/LandingLayout.vue";
import OrderDetail from "./Order/OrderDetail.vue";

const props = defineProps({
    transaction: {
        type: Object as () => TransactionEntity,
        required: true,
    },
    groups: {
        type: Array as () => OrderGroupModel[],
        required: true,
    },
});
</script>

<template>
    <LandingLayout title="Berhasil Membuat Pesanan">
        <div
            class="p-6 sm:p-12 md:px-[100px] md:py-[60px] flex flex-col gap-2 sm:gap-3"
        >
            <h1
                class="text-2xl font-bold text-start sm:text-center sm:text-3xl"
            >
                Pesanan Berhasil!
            </h1>

            <p
                class="text-sm text-gray-700 text-start sm:text-center sm:text-base"
            >
                Terima kasih telah melakukan pemesanan. Pesanan Anda telah
                berhasil dibuat.
            </p>
        </div>

        <OrderDetail :transaction="props.transaction" :groups="props.groups" />
    </LandingLayout>
</template>

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
    <LandingLayout title="Order Success">
        <div
            class="p-6 sm:p-12 md:px-[100px] md:py-[60px] flex flex-col gap-2 sm:gap-3 sm:items-center"
        >
            <h1
                class="text-2xl font-bold text-start sm:text-center sm:text-3xl"
            >
                Detail Pesanan Anda
            </h1>

            <p
                class="max-w-lg text-sm text-gray-700 text-start sm:text-center sm:text-base"
            >
                Terima kasih telah melakukan pemesanan di Mustika Collection.
                Silakan cek detail pesanan Anda di bawah ini.
            </p>
        </div>

        <OrderDetail :transaction="props.transaction" :groups="props.groups">
            <template #actions v-if="showPaymentActions">
                <!-- Payment Buttons -->
                <div class="flex flex-col gap-2 mt-2">
                    <PrimaryButton
                        class="w-full py-3"
                        :disabled="resumePaymentStatus === 'loading'"
                        @click="showSnap()"
                    >
                        Lanjutkan Pembayaran
                    </PrimaryButton>
                    <SecondaryButton
                        v-if="payment?.midtrans_response"
                        class="w-full py-3"
                        :disabled="resumePaymentStatus === 'loading'"
                        @click="changePaymentType()"
                    >
                        Ubah Tipe Pembayaran
                    </SecondaryButton>
                </div>
            </template>
        </OrderDetail>
    </LandingLayout>
</template>

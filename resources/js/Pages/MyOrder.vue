<script setup lang="ts">
import LandingSection from "@/Components/LandingSection.vue";
import Order from "./Order/Order.vue";
import LandingLayout from "@/Layouts/LandingLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Link } from "@inertiajs/vue3";
import JoinUs from "@/Components/JoinUs.vue";

const props = defineProps({
    transactions: {
        type: Object as () => {
            data: TransactionEntity[];
            links: {
                url: string | null;
                label: string;
                active: boolean;
            }[];
            meta: {
                current_page: number;
                from: number;
                last_page: number;
            };
        },
        required: true,
    },
});

const transactions = props.transactions.data;
</script>

<template>
    <LandingLayout title="Pesanan Saya">
        <div
            class="p-6 sm:p-12 md:px-[100px] md:py-[60px] flex flex-col gap-2 sm:gap-3 sm:items-center"
            :class="{
                'min-h-[60vh] items-center justify-center gap-4':
                    transactions.length == 0,
            }"
        >
            <h1
                class="text-2xl font-bold text-start sm:text-center sm:text-3xl"
            >
                {{
                    transactions.length > 0
                        ? "Pesanan Saya"
                        : "Belum Ada Pesanan"
                }}
            </h1>
            <div
                v-if="transactions.length == 0"
                class="flex flex-col items-center gap-y-6"
            >
                <p
                    class="max-w-md text-sm text-center text-gray-700 sm:text-base"
                >
                    Saat ini Anda belum memiliki pesanan. Silakan mulai belanja
                    untuk melihat pesanan Anda di sini.
                </p>
                <Link :href="route('catalog')">
                    <PrimaryButton class="py-2.5 px-5 mx-auto">
                        Mulai Belanja
                    </PrimaryButton>
                </Link>
            </div>
            <p
                class="max-w-md text-sm text-gray-700 text-start sm:text-center sm:text-base"
                v-else
            >
                Terima kasih telah melakukan pemesanan. Silakan cek detail
                pesanan Anda di bawah ini.
            </p>
        </div>

        <div
            data-aos="fade-up"
            data-aos-duration="600"
            class="p-6 !pt-0 sm:p-12 md:p-[100px] flex flex-col gap-4 sm:gap-12"
        >
            <LandingSection
                v-if="transactions.length > 0"
                class="!flex-col !justify-start !min-h-[56vh]"
            >
                <div
                    class="flex flex-col items-center justify-center w-full gap-2 mx-auto lg:flex-row lg:items-start sm:gap-12 max-w-7xl"
                >
                    <!-- Items -->
                    <div class="flex flex-col gap-4 sm:gap-6">
                        <Order
                            v-for="(transaction, index) in transactions"
                            :key="index"
                            :transaction="transaction"
                            :showDivider="false"
                        />
                    </div>
                </div>
            </LandingSection>
        </div>
    </LandingLayout>
</template>

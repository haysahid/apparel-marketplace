<script setup lang="ts">
import LandingLayout from "@/Layouts/LandingLayout.vue";
import OrderTransactionDetail from "./Order/OrderTransactionDetail.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { ref, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import OrderContentRow from "@/Components/OrderContentRow.vue";
import StatusChip from "@/Components/StatusChip.vue";
import midtransPayment from "@/plugins/midtrans-payment";

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

const checkPaymentStatus = ref(null);
function checkPayment() {
    midtransPayment.checkPayment(
        {
            transactionCode: props.transaction.code,
            isGuest: false,
        },
        {
            onSuccess: (response) => {
                payment.value = response.data.result;
                router.reload();
            },
            onChangeStatus: (status) => {
                checkPaymentStatus.value = status;
            },
        },
    );
}

function showSnap() {
    midtransPayment.showSnap(
        {
            snapToken: payment.value.midtrans_snap_token,
        },
        {
            onSuccess: (result) => {
                checkPayment();
            },
            onPending: (result) => {
                checkPayment();
            },
            onError: (error) => {
                checkPayment();
            },
            onClose: () => {
                checkPayment();
            },
            onChangeStatus: (status) => {
                resumePaymentStatus.value = status;
            },
        },
    );
}

function changePaymentType() {
    midtransPayment.changePaymentType(
        {
            transactionCode: props.transaction.code,
        },
        {
            onSuccess: (response) => {
                payment.value = response.data.result;

                if (payment.value.midtrans_snap_token) {
                    showSnap();
                }
            },
        },
    );
}

// Check payment
const payment = ref(null);
const resumePaymentStatus = ref(null);

if (props.transaction.payments) {
    payment.value = props.transaction.payments[0];
}

const showPaymentActions = computed(() => {
    return (
        props.transaction.status === "pending" &&
        props.transaction.payment_method.slug === "transfer"
    );
});

if (showPaymentActions.value) {
    checkPayment();
}

onMounted(() => {
    if (route().params?.transaction_status == "settlement") {
        // Reload the page
        router.visit(
            route("my-order.detail", {
                transaction_code: props.transaction.code,
            }),
        );
    } else if (
        route().params?.show_snap == "1" &&
        props.transaction.status == "pending"
    ) {
        showSnap();
    }
});
</script>

<template>
    <LandingLayout title="Detail Pesanan">
        <div
            class="p-6 sm:p-12 md:px-[100px] md:py-[60px] flex flex-col gap-2 sm:gap-3 sm:items-center bg-secondary-box"
        >
            <h1 class="text-xl font-bold text-start sm:text-center">
                Detail Pesanan Anda
            </h1>

            <p class="max-w-lg text-sm text-gray-700 text-start sm:text-center">
                Terima kasih telah melakukan pemesanan. Silakan cek detail
                pesanan Anda di bawah ini.
            </p>
        </div>

        <OrderTransactionDetail
            :transaction="props.transaction"
            :groups="props.groups"
            class="mt-12"
        >
            <template #additionalInfo>
                <!-- Payment -->
                <template v-if="showPaymentActions">
                    <div class="my-2 border-b border-gray-200"></div>
                    <OrderContentRow
                        label="Status Pembayaran"
                        :value="payment?.status"
                    >
                        <template #value>
                            <StatusChip
                                :status="payment.status"
                                :label="payment.status?.toUpperCase()"
                            />
                        </template>
                    </OrderContentRow>
                    <OrderContentRow
                        v-if="payment?.midtrans_response"
                        label="Tipe Pembayaran"
                        :value="
                            payment?.midtrans_response?.payment_type
                                ?.split('_')
                                .map(
                                    (word) =>
                                        word.charAt(0).toUpperCase() +
                                        word.slice(1),
                                )
                                .join(' ')
                        "
                    />
                    <OrderContentRow
                        v-if="payment?.midtrans_response?.va_numbers"
                        label="Tujuan Pembayaran"
                        :value="
                            payment?.midtrans_response?.va_numbers[0]?.bank?.toUpperCase()
                        "
                    />
                    <OrderContentRow
                        v-if="payment?.midtrans_response"
                        label="Batas Akhir Pembayaran"
                        :value="payment?.midtrans_response?.expiry_time"
                    />
                </template>

                <!-- Shipping Address -->
                <template
                    v-if="props.transaction.shipping_method.slug === 'courier'"
                >
                    <div class="my-2 border-b border-gray-200"></div>
                    <OrderContentRow
                        label="Provinsi"
                        :value="props.transaction.province_name"
                    />
                    <OrderContentRow
                        label="Kota"
                        :value="props.transaction.city_name"
                    />
                    <OrderContentRow
                        label="Alamat"
                        :value="props.transaction.address"
                    />
                </template>
            </template>

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
        </OrderTransactionDetail>
    </LandingLayout>
</template>

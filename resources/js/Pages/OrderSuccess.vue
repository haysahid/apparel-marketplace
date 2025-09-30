<script setup lang="ts">
import LandingLayout from "@/Layouts/LandingLayout.vue";
import OrderDetail from "./Order/OrderDetail.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { ref, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import OrderContentRow from "@/Components/OrderContentRow.vue";
import StatusChip from "@/Components/StatusChip.vue";
import SuccessView from "@/Components/SuccessView.vue";
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
    isGuest: {
        type: Boolean,
        required: false,
        default: false,
    },
});

function checkPayment() {
    midtransPayment.checkPayment(
        {
            transactionCode: props.transaction.code,
            isGuest: props.isGuest,
        },
        {
            onSuccess: (response) => {
                payment.value = response.data.result;

                if (props.isGuest) {
                    router.reload();
                } else if (payment.value.status !== "pending") {
                    router.visit(route("my-order"));
                }
            },
            onError: (error) => {
                console.error("Error checking payment:", error);
            },
        }
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
        }
    );
}

function changePaymentType() {
    midtransPayment.changePaymentType(
        {
            transactionCode: props.transaction.code,
        },
        {
            onSuccess: async (response) => {
                payment.value = response.data.result;

                if (payment.value.midtrans_snap_token) {
                    showSnap();
                }
            },
            onError: (error) => {
                console.error("Error changing payment type:", error);
            },
        }
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

onMounted(() => {
    if (route().params?.transaction_status == "settlement") {
        if (props.isGuest) {
            router.reload();
        } else {
            router.visit(
                route("my-order.detail", {
                    transaction_code: props.transaction.code,
                })
            );
        }
    } else if (props.transaction.status == "pending") {
        if (route().params?.show_snap == "1") {
            showSnap();
        } else {
            checkPayment();
        }
    }
});
</script>

<template>
    <LandingLayout title="Berhasil Membuat Pesanan">
        <div
            class="p-6 sm:px-12 md:px-[100px] flex items-center justify-center"
        >
            <SuccessView
                :order-number="props.transaction.code"
                title="Pesanan Berhasil Dibuat!"
                subtitle="Terima kasih telah melakukan pemesanan. Pesanan Anda telah berhasil dibuat."
            />
        </div>

        <OrderDetail
            data-aos="fade-up"
            data-aos-delay="2000"
            data-aos-duration="600"
            data-aos-once="true"
            :transaction="props.transaction"
            :groups="props.groups"
            :showTracking="false"
            class="p-6 !pt-0 sm:p-12 md:p-[100px] flex flex-col gap-4"
            @continuePayment="showSnap()"
        >
            <template #additionalInfo>
                <!-- Payment -->
                <template v-if="showPaymentActions">
                    <div class="my-2 border-b border-gray-300"></div>
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
                                        word.slice(1)
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
                    <div class="my-2 border-b border-gray-300"></div>
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
        </OrderDetail>
    </LandingLayout>
</template>

<script setup lang="ts">
import LandingLayout from "@/Layouts/LandingLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import OrderContentRow from "@/Components/OrderContentRow.vue";
import StatusChip from "@/Components/StatusChip.vue";
import InvoiceDetail from "./MyStore/Order/InvoiceDetail.vue";

async function initScript() {
    const snapScript = "https://app.sandbox.midtrans.com/snap/snap.js";
    const clientKey = import.meta.env.VITE_MIDTRANS_CLIENT_KEY;

    const script = document.createElement("script");
    script.src = snapScript;
    script.setAttribute("data-client-key", clientKey);
    script.async = true;

    document.body.appendChild(script);

    return () => {
        document.body.removeChild(script);
    };
}

const props = defineProps({
    invoice: {
        type: Object as () => InvoiceEntity,
        required: true,
    },
    items: {
        type: Array as () => TransactionItemEntity[],
        required: true,
    },
    payments: {
        type: Array as () => PaymentEntity[],
        required: true,
    },
});

// Check payment
const payment = ref(null);

async function checkPayment() {
    await axios
        .get(
            `/api/check-payment?transaction_code=${props.invoice.transaction.code}`,
            {
                headers: {
                    authorization: `Bearer ${localStorage.getItem(
                        "access_token"
                    )}`,
                },
            }
        )
        .then((response) => {
            payment.value = response.data.result;
        });
}

if (props.payments) {
    payment.value = props.payments[0];
}

const showPaymentActions = computed(() => {
    return (
        props.invoice.status === "pending" &&
        props.invoice.transaction.payment_method.slug === "transfer"
    );
});

if (showPaymentActions.value) {
    checkPayment();
}

// Resume Payment
const resumePaymentStatus = ref(null);

async function showSnap() {
    resumePaymentStatus.value = "loading";

    if (!window.snap) {
        await initScript();
    }

    setTimeout(async () => {
        const snapToken = payment.value.midtrans_snap_token;

        if (!snapToken) {
            console.error("Snap token is not available");
            return;
        }

        await window.snap.pay(snapToken, {
            onSuccess: async function (result) {
                window.scrollTo({
                    top: 0,
                    behavior: "smooth",
                });

                await axios
                    .post(
                        "/api/confirm-payment",
                        {
                            payment_id: payment.value.id,
                        },
                        {
                            headers: {
                                authorization: `Bearer ${localStorage.getItem(
                                    "access_token"
                                )}`,
                            },
                        }
                    )
                    .then((response) => {
                        resumePaymentStatus.value = "success";
                        router.visit(
                            route("my-order.detail", {
                                transaction_code:
                                    props.invoice.transaction.code,
                            })
                        );
                    })
                    .catch((error) => {
                        resumePaymentStatus.value = "error";
                    });
            },
            onPending: async function (result) {
                resumePaymentStatus.value = "pending";
                await checkPayment();
            },
            onError: async function (result) {
                resumePaymentStatus.value = "error";
                await checkPayment();
            },
            onClose: async function () {
                resumePaymentStatus.value = "error";
                await checkPayment();
            },
        });
    }, 1000);
}

// Change Payment Type
async function changePaymentType() {
    await axios
        .put(
            "/api/change-payment-type",
            {
                transaction_code: props.invoice.code,
            },
            {
                headers: {
                    authorization: `Bearer ${localStorage.getItem(
                        "access_token"
                    )}`,
                },
            }
        )
        .then(async (response) => {
            payment.value = response.data.result;

            if (payment.value.midtrans_snap_token) {
                await showSnap();
            }
        });
}

onMounted(() => {
    if (route().params?.transaction_status == "settlement") {
        // Reload the page
        router.visit(
            route("my-order.detail", {
                transaction_code: props.invoice.transaction.code,
            })
        );
    } else if (
        route().params?.show_snap == "1" &&
        props.invoice.status == "pending"
    ) {
        showSnap();
    }
});
</script>

<template>
    <LandingLayout title="Detail Pesanan Saya">
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

        <InvoiceDetail
            data-aos="fade-up"
            data-aos-duration="600"
            data-aos-once="true"
            :invoice="props.invoice"
            :items="props.items"
            :showTracking="
                props.invoice.status !== 'cancelled' &&
                (!showPaymentActions ||
                    props.invoice.transaction.payment_method.slug === 'cod')
            "
            :isShowingFromMyStore="false"
            class="p-6 !pt-0 sm:p-12 md:p-[100px] flex flex-col gap-4 mt-6"
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
                    v-if="
                        props.invoice.transaction.shipping_method.slug ===
                        'courier'
                    "
                >
                    <div class="my-2 border-b border-gray-300"></div>
                    <OrderContentRow
                        label="Provinsi"
                        :value="props.invoice.transaction.province_name"
                    />
                    <OrderContentRow
                        label="Kota"
                        :value="props.invoice.transaction.city_name"
                    />
                    <OrderContentRow
                        label="Alamat"
                        :value="props.invoice.transaction.address"
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
        </InvoiceDetail>
    </LandingLayout>
</template>

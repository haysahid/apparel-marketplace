<script setup lang="ts">
import LandingLayout from "@/Layouts/LandingLayout.vue";
import OrderDetail from "./Order/OrderDetail.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import OrderContentRow from "@/Components/OrderContentRow.vue";
import OrderStatusChip from "./Order/OrderStatusChip.vue";
import SuccessView from "@/Components/SuccessView.vue";

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

// Check payment
const payment = ref(null);

async function checkPayment() {
    await axios
        .get(`/api/check-payment?transaction_code=${props.transaction.code}`, {
            headers: {
                authorization: `Bearer ${localStorage.getItem("access_token")}`,
            },
        })
        .then((response) => {
            payment.value = response.data.result;
        });
}

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

// Resume Payment
const resumePaymentStatus = ref(null);

async function showSnap() {
    console.log("showSnap called");
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
                        if (props.isGuest) {
                            router.reload();
                        } else {
                            router.visit(route("my-order"));
                        }
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
                transaction_code: props.transaction.code,
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
        if (props.isGuest) {
            router.reload();
        } else {
            router.visit(
                route("my-order.detail", {
                    transaction_code: props.transaction.code,
                })
            );
        }
    } else if (
        route().params?.show_snap == 1 &&
        props.transaction.status == "pending"
    ) {
        showSnap();
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
            :transaction="props.transaction"
            :groups="props.groups"
            :showTracking="false"
            class="p-6 !pt-0 sm:p-12 md:p-[100px] flex flex-col gap-4 sm:gap-12"
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
                            <OrderStatusChip
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

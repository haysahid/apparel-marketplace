<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import OrderTransactionDetail from "@/Pages/Order/OrderTransactionDetail.vue";
import ChangeTransactionStatusDialog from "./ChangeTransactionStatusDialog.vue";
import axios from "axios";
import OrderContentRow from "@/Components/OrderContentRow.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import SuccessView from "@/Components/SuccessView.vue";
import { router } from "@inertiajs/vue3";
import midtransPayment from "@/plugins/midtrans-payment";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import StatusChip from "@/Components/StatusChip.vue";
import cookieManager from "@/plugins/cookie-manager";

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
        }
    );
}

function showSnap() {
    if (!payment.value.midtrans_snap_token) {
        changePaymentType();
        return;
    }

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
            onSuccess: (response) => {
                payment.value = response.data.result;

                if (payment.value.midtrans_snap_token) {
                    showSnap();
                }
            },
        }
    );
}

// Check payment
const payment = ref(null);
const resumePaymentStatus = ref(null);

const showPaymentActions = computed(() => {
    return (
        props.transaction.status === "pending" &&
        props.transaction.payment_method.slug === "transfer"
    );
});

onMounted(() => {
    if (props.transaction.payments) {
        payment.value = props.transaction.payments[0];
    }

    if (route().params?.transaction_status == "settlement") {
        router.reload();
    } else if (props.transaction.status == "pending") {
        if (route().params?.success == "true") {
            showSnap();
        } else {
            checkPayment();
        }
    }
});

// Change Status

const showChangeStatusDialog = ref(false);

function changeStatus(newStatus: string) {
    const token = `Bearer ${cookieManager.getItem("access_token")}`;

    axios
        .put(
            "/api/admin/change-order-status",
            {
                transaction_id: props.transaction.id,
                status: newStatus,
            },
            {
                headers: {
                    Authorization: token,
                },
            }
        )
        .then(() => {
            window.location.reload();
        })
        .catch((error) => {
            console.error("Error changing transaction status:", error);
        });
}

window.onpopstate = function () {
    router.reload();
};

const showSuccessView = route().params.success == "true";
</script>

<template>
    <MyStoreLayout
        title="Detail Transaksi"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Transaksi', url: '/my-store/transaction', active: false },
            { text: props.transaction.code, active: true },
        ]"
    >
        <div class="flex flex-col gap-6">
            <DefaultCard v-if="showSuccessView" isMain>
                <SuccessView title="Transaksi Berhasil!" />
            </DefaultCard>

            <OrderTransactionDetail
                :data-aos="showSuccessView ? 'fade-up' : 'none'"
                data-aos-delay="1600"
                data-aos-duration="600"
                data-aos-once="true"
                :transaction="props.transaction"
                :groups="props.groups"
                :isShowingFromMyStore="true"
                :isLoading="checkPaymentStatus === 'loading'"
                class="!px-0"
                @continuePayment="showSnap()"
            >
                <template #additionalInfo>
                    <!-- Payment -->
                    <template v-if="payment">
                        <div class="my-2 border-b border-gray-200"></div>
                        <h3 class="font-semibold text-gray-800">
                            Informasi Pembayaran
                        </h3>
                        <OrderContentRow
                            label="Status Pembayaran"
                            :value="payment?.status"
                            :isLoading="checkPaymentStatus === 'loading'"
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
                            props.transaction.shipping_method.slug === 'courier'
                        "
                    >
                        <div class="my-2 border-b border-gray-200"></div>
                        <h3 class="font-semibold text-gray-800">
                            Alamat Pengiriman
                        </h3>
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
                        <OrderContentRow
                            label="Kode Pos"
                            :value="props.transaction.zip_code"
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

                    <!-- <PrimaryButton
                            @click="showChangeStatusDialog = true"
                            class="w-full"
                        >
                            Ubah Status
                        </PrimaryButton> -->
                </template>
            </OrderTransactionDetail>
        </div>

        <ChangeTransactionStatusDialog
            :show="showChangeStatusDialog"
            :currentStatus="props.transaction.status"
            :options="[
                {
                    value: 'pending',
                    label: 'PENDING',
                    disabled: false,
                },
                {
                    value: 'paid',
                    label: 'PAID',
                    disabled: false,
                },
                {
                    value: 'processing',
                    label: 'PROCESSING',
                    disabled: false,
                },
                {
                    value: 'completed',
                    label: 'COMPLETED',
                    disabled: false,
                },
                {
                    value: 'cancelled',
                    label: 'CANCELLED',
                    disabled: false,
                },
            ]"
            @change="
                showChangeStatusDialog = false;
                if ($event != props.transaction.status) {
                    changeStatus($event);
                }
            "
            @close="showChangeStatusDialog = false"
        />
    </MyStoreLayout>
</template>

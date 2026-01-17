<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ChangeTransactionStatusDialog from "./ChangeTransactionStatusDialog.vue";
import axios from "axios";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import InvoiceDetail from "./InvoiceDetail.vue";
import OrderContentRow from "@/Components/OrderContentRow.vue";
import StatusChip from "@/Components/StatusChip.vue";
import cookieManager from "@/plugins/cookie-manager";
import midtransPayment from "@/plugins/midtrans-payment";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import invoiceService from "@/services/my-store/invoice-service";
import ShipmentForm from "../Invoice/ShipmentForm.vue";
import DialogModal from "@/Components/DialogModal.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

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

const transaction = computed(() => props.invoice.transaction);

const checkPaymentStatus = ref(null);
function checkPayment() {
    midtransPayment.checkPayment(
        {
            transactionCode: transaction.value.code,
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
            transactionCode: transaction.value.code,
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
        payment.value &&
        transaction.value.status === "pending" &&
        transaction.value.payment_method.slug === "transfer"
    );
});

// Change Status
const showChangeStatusDialog = ref(false);
const changeStatusStatus = ref(null);

function changeStatus(newStatus: string) {
    const token = `Bearer ${cookieManager.getItem("access_token")}`;

    changeStatusStatus.value = "loading";

    axios
        .put(
            "/api/my-store/change-order-status",
            {
                invoice_id: props.invoice.id,
                status: newStatus,
            },
            {
                headers: {
                    Authorization: token,
                },
            }
        )
        .then(() => {
            changeStatusStatus.value = "success";
            window.location.reload();
        })
        .catch((error) => {
            changeStatusStatus.value = "error";
            console.error("Error changing transaction status:", error);
        });
}

// Set Completed
const showSetCompletedConfirmationDialog = ref(false);

// Shipping
const showShippingActions = computed(() => {
    if (transaction.value.payment_method.slug === "cod") {
        return (
            props.invoice.status === "pending" &&
            transaction.value.shipping_method.slug === "courier"
        );
    }

    return (
        props.invoice.status === "paid" &&
        transaction.value.shipping_method.slug === "courier"
    );
});
const showShipmentFormDialog = ref(false);

// Completed
const showCompletedActions = computed(() => {
    return (
        props.invoice.status === "processing" &&
        transaction.value.shipping_method.slug === "courier"
    );
});

// Shipments
const shipments = ref<ShipmentEntity[]>([]);

const getShipments = async () => {
    invoiceService().getShipments(props.invoice.id, {
        onSuccess: (response) => {
            shipments.value = response.data.result;
        },
    });
};

onMounted(() => {
    if (props.payments.length) {
        payment.value = props.payments[0];
    }

    if (route().params?.transaction_status == "settlement") {
        router.reload();
    } else if (transaction.value.status == "pending") {
        if (route().params?.success == "true") {
            showSnap();
        } else {
            checkPayment();
        }
    }

    getShipments();
});

window.onpopstate = function () {
    router.reload();
};
</script>

<template>
    <MyStoreLayout
        title="Detail Pesanan"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Pesanan', url: '/my-store/order', active: false },
            { text: props.invoice.code, active: true },
        ]"
    >
        <InvoiceDetail
            :invoice="props.invoice"
            :items="props.items"
            :shipments="shipments"
            :showTracking="props.invoice.status !== 'cancelled'"
            :isShowingFromMyStore="true"
        >
            <template #additionalInfo>
                <!-- Payment -->
                <template v-if="showPaymentActions">
                    <div class="my-2 border-b border-gray-200"></div>
                    <h3 class="font-semibold text-gray-800">
                        Informasi Pembayaran
                    </h3>
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
                    <div class="my-2 border-b border-gray-200"></div>
                    <h3 class="font-semibold text-gray-800">
                        Alamat Pengiriman
                    </h3>
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
                    <OrderContentRow
                        label="Kode Pos"
                        :value="props.invoice.transaction.zip_code"
                    />
                </template>
            </template>

            <template #actions>
                <!-- Payment Buttons -->
                <div
                    v-if="
                        showPaymentActions ||
                        showShippingActions ||
                        showCompletedActions
                    "
                    class="flex flex-col gap-2 mt-2"
                >
                    <template v-if="showPaymentActions">
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
                    </template>
                    <template v-if="showShippingActions">
                        <PrimaryButton
                            class="w-full py-3"
                            :disabled="changeStatusStatus === 'loading'"
                            @click="showShipmentFormDialog = true"
                        >
                            Lanjutkan Pengiriman
                        </PrimaryButton>
                    </template>
                    <template v-if="showCompletedActions">
                        <PrimaryButton
                            class="w-full py-3"
                            :disabled="changeStatusStatus === 'loading'"
                            @click="showSetCompletedConfirmationDialog = true"
                        >
                            Selesai
                        </PrimaryButton>
                    </template>
                </div>

                <!-- <PrimaryButton
                        @click="showChangeStatusDialog = true"
                        class="w-full py-3"
                    >
                        Ubah Status
                    </PrimaryButton> -->
            </template>
        </InvoiceDetail>

        <!-- Shipment Form Dialog -->
        <DialogModal
            :show="showShipmentFormDialog"
            @close="showShipmentFormDialog = false"
            maxWidth="md"
        >
            <template #title> Tambah Informasi Pengiriman </template>
            <template #content>
                <ShipmentForm
                    :invoice="props.invoice"
                    :items="props.items"
                    :isDialog="true"
                    class="w-full"
                    @close="showShipmentFormDialog = false"
                    @submitted="
                        showShipmentFormDialog = false;
                        router.reload();
                        getShipments();
                    "
                />
            </template>
        </DialogModal>

        <!-- Set Complete Confirmation -->
        <DialogModal
            :show="showSetCompletedConfirmationDialog"
            @close="showSetCompletedConfirmationDialog = false"
            maxWidth="sm"
        >
            <template #title>
                <h3
                    class="text-lg font-medium leading-6 text-gray-900 text-wrap"
                >
                    Tandai Pesanan Sebagai Selesai?
                </h3>
            </template>
            <template #content>
                <p class="mt-0.5 mb-1.5 text-center text-wrap">
                    Apakah Anda yakin ingin menandai pesanan ini sebagai
                    selesai? Tindakan ini tidak dapat dibatalkan.
                </p>
            </template>
            <slot />
            <template #footer>
                <div class="flex gap-4 text-base">
                    <SecondaryButton
                        type="button"
                        class=""
                        @click="showSetCompletedConfirmationDialog = false"
                    >
                        Batalkan
                    </SecondaryButton>
                    <PrimaryButton
                        type="button"
                        @click="
                            showSetCompletedConfirmationDialog = false;
                            changeStatus('completed');
                        "
                    >
                        Ya, Selesaikan
                    </PrimaryButton>
                </div>
            </template>
        </DialogModal>

        <ChangeTransactionStatusDialog
            :show="showChangeStatusDialog"
            :currentStatus="props.invoice.status"
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
                if ($event != props.invoice.status) {
                    changeStatus($event);
                }
            "
            @close="showChangeStatusDialog = false"
        />
    </MyStoreLayout>
</template>

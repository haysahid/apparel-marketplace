<script setup lang="ts">
import { ref, computed } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ChangeTransactionStatusDialog from "./ChangeTransactionStatusDialog.vue";
import axios from "axios";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import InvoiceDetail from "./InvoiceDetail.vue";
import OrderContentRow from "@/Components/OrderContentRow.vue";
import StatusChip from "@/Components/StatusChip.vue";
import cookieManager from "@/plugins/cookie-manager";

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

const showChangeStatusDialog = ref(false);

function changeStatus(newStatus: string) {
    const token = `Bearer ${cookieManager.getItem("access_token")}`;

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
            window.location.reload();
        })
        .catch((error) => {
            console.error("Error changing transaction status:", error);
        });
}

const showPaymentActions = computed(() => {
    return true;

    return (
        props.invoice.transaction.status === "pending" &&
        props.invoice.transaction.payment_method.slug === "transfer"
    );
});

const payment = computed(() => {
    return props.payments.length > 0 ? props.payments[0] : null;
});

window.onpopstate = function () {
    location.reload();
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
        <DefaultCard :isMain="true">
            <InvoiceDetail
                :invoice="props.invoice"
                :items="props.items"
                class="!px-0 !pt-0 md:!px-0"
                :showTracking="props.invoice.status !== 'cancelled'"
                :isShowingFromMyStore="true"
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

                <template #actions v-if="props.invoice.status !== 'cancelled'">
                    <PrimaryButton
                        @click="showChangeStatusDialog = true"
                        class="w-full py-3"
                    >
                        Ubah Status
                    </PrimaryButton>
                </template>
            </InvoiceDetail>
        </DefaultCard>

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

<script setup lang="ts">
import OrderContentRow from "@/Components/OrderContentRow.vue";
import OrderStatusChip from "./OrderStatusChip.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    transaction: {
        type: Object as () => TransactionEntity,
        required: true,
    },
    groups: {
        type: Array as () => OrderGroupModel[],
        required: true,
    },
    payment: {
        type: Object as () => PaymentEntity | null,
        default: null,
    },
    showPaymentActions: {
        type: Boolean,
        default: true,
    },
});

const subTotal = props.groups.reduce(
    (total, group) =>
        total + (group.invoice.amount - group.invoice.shipping_cost),
    0
);
const total = props.groups.reduce(
    (total, group) => total + group.invoice.amount,
    0
);
</script>

<template>
    <div
        class="w-full lg:w-[480px] outline outline-1 -outline-offset-1 outline-gray-300 rounded-2xl p-4 gap-y-4 flex flex-col gap-4"
    >
        <h3 class="text-lg font-semibold text-gray-700">Ringkasan Pemesanan</h3>
        <div>
            <div class="flex flex-col gap-2">
                <OrderContentRow
                    label="Kode Pemesanan"
                    :value="props.transaction.code"
                />

                <OrderContentRow
                    label="Tgl. Pemesanan"
                    :value="$formatDate(props.transaction.created_at)"
                />
                <OrderContentRow
                    label="Status"
                    :value="props.transaction.status"
                >
                    <template #value>
                        <!-- Status -->
                        <OrderStatusChip
                            :status="props.transaction.status"
                            :label="props.transaction.status?.toUpperCase()"
                        />
                    </template>
                </OrderContentRow>
                <OrderContentRow
                    label="Metode Pembayaran"
                    :value="props.transaction.payment_method.name"
                />
                <OrderContentRow
                    label="Metode Pengiriman"
                    :value="props.transaction.shipping_method.name"
                />

                <!-- Payment -->
                <template v-if="props.showPaymentActions">
                    <!-- Divider -->
                    <div class="my-2 border-b border-gray-300"></div>
                    <OrderContentRow
                        label="Status Pembayaran"
                        :value="props.payment?.status"
                    >
                        <template #value>
                            <OrderStatusChip
                                :status="props.payment.status"
                                :label="props.payment.status?.toUpperCase()"
                            />
                        </template>
                    </OrderContentRow>
                    <OrderContentRow
                        v-if="props.payment?.midtrans_response"
                        label="Tipe Pembayaran"
                        :value="
                            props.payment?.midtrans_response?.payment_type
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
                        v-if="props.payment?.midtrans_response?.va_numbers"
                        label="Tujuan Pembayaran"
                        :value="
                            props.payment?.midtrans_response?.va_numbers[0]?.bank?.toUpperCase()
                        "
                    />
                    <OrderContentRow
                        v-if="props.payment?.midtrans_response"
                        label="Batas Akhir Pembayaran"
                        :value="props.payment?.midtrans_response?.expiry_time"
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

                <!-- Additional Information -->
                <template v-if="$slots.additionalInfo">
                    <div class="my-2 border-b border-gray-300"></div>
                    <slot name="additionalInfo" />
                </template>

                <!-- Divider -->
                <div class="my-2 border-b border-gray-300"></div>

                <OrderContentRow
                    label="Sub Total"
                    :value="$formatCurrency(subTotal)"
                />
                <OrderContentRow
                    label="Biaya Pengiriman"
                    :value="$formatCurrency(props.transaction.shipping_cost)"
                />
                <div class="flex items-center justify-between">
                    <p class="text-lg font-bold text-gray-700">
                        {{
                            props.transaction.status !== "paid"
                                ? "Total Tagihan"
                                : "Total"
                        }}
                    </p>
                    <p class="text-lg font-bold text-primary">
                        {{ $formatCurrency(total) }}
                    </p>
                </div>

                <!-- Payment Buttons -->
                <div
                    v-if="props.showPaymentActions"
                    class="flex flex-col gap-2 mt-2"
                >
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

                <!-- Actions -->
                <div v-if="$slots.actions" class="mt-1">
                    <slot name="actions" />
                </div>
            </div>
        </div>
    </div>
</template>

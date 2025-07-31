<script setup lang="ts">
import OrderContentRow from "@/Components/OrderContentRow.vue";
import OrderStatusChip from "./OrderStatusChip.vue";

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

const subTotal = props.groups.reduce(
    (total, group) => total + group.invoice.base_amount,
    0
);

const discount = props.groups.reduce(
    (total, group) => total + group.invoice.voucher_amount,
    0
);

const total = props.groups.reduce(
    (total, group) => total + group.invoice.amount,
    0
);
</script>

<template>
    <div
        class="w-full xl:w-[480px] outline outline-1 -outline-offset-1 outline-gray-300 rounded-2xl p-4 gap-y-4 flex flex-col gap-4"
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

                <!-- Additional Information -->
                <template v-if="$slots.additionalInfo">
                    <slot name="additionalInfo" />
                </template>

                <!-- Divider -->
                <div class="my-2 border-b border-gray-300"></div>

                <OrderContentRow
                    label="Sub Total"
                    :value="$formatCurrency(subTotal)"
                />
                <OrderContentRow
                    label="Voucher Diskon"
                    :value="`-${$formatCurrency(discount)}`"
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

                <!-- Actions -->
                <div v-if="$slots.actions" class="mt-1">
                    <slot name="actions" />
                </div>
            </div>
        </div>
    </div>
</template>

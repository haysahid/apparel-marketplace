<script setup lang="ts">
import CopyButton from "@/Components/CopyButton.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import OrderContentRow from "@/Components/OrderContentRow.vue";
import StatusChip from "@/Components/StatusChip.vue";

const props = defineProps({
    invoice: {
        type: Object as () => InvoiceEntity,
        required: true,
    },
    items: {
        type: Array as () => TransactionItemEntity[],
        required: true,
    },
});

const subTotal = props.invoice.base_amount ?? 0;
const discount = props.invoice.voucher_amount ?? 0;
const total = subTotal - discount + (props.invoice.shipping_cost ?? 0);
</script>

<template>
    <DefaultCard
        :isMain="true"
        class="flex flex-col w-full gap-4 xl:max-w-sm gap-y-3"
    >
        <h3 class="font-semibold text-gray-800">Ringkasan Pemesanan</h3>
        <div>
            <div class="flex flex-col gap-2">
                <OrderContentRow
                    label="No. Invoice"
                    :value="props.invoice.code"
                >
                    <template #value>
                        <div class="flex items-center gap-0.5">
                            <p
                                class="text-sm font-semibold text-right text-gray-700"
                            >
                                {{ props.invoice.code }}
                            </p>
                            <CopyButton
                                :id="`copy-code-tooltip-invoice-${props.invoice.id}`"
                                :text="props.invoice.code"
                            />
                        </div>
                    </template>
                </OrderContentRow>

                <OrderContentRow
                    label="Tgl. Pemesanan"
                    :value="$formatDate(props.invoice.created_at)"
                />
                <OrderContentRow label="Status" :value="props.invoice.status">
                    <template #value>
                        <!-- Status -->
                        <StatusChip
                            :status="props.invoice.status"
                            :label="props.invoice.status?.toUpperCase()"
                        />
                    </template>
                </OrderContentRow>
                <OrderContentRow
                    label="Metode Pembayaran"
                    :value="props.invoice.transaction.payment_method.name"
                />
                <OrderContentRow
                    label="Metode Pengiriman"
                    :value="props.invoice.transaction.shipping_method.name"
                />

                <!-- Additional Information -->
                <template v-if="$slots.additionalInfo">
                    <slot name="additionalInfo" />
                </template>

                <!-- Divider -->
                <div class="my-2 border-b border-gray-200"></div>

                <OrderContentRow
                    label="Sub Total"
                    :value="$formatCurrency(subTotal)"
                />
                <OrderContentRow
                    label="Voucher Diskon"
                    :value="`- ${$formatCurrency(discount)}`"
                />
                <OrderContentRow
                    label="Biaya Pengiriman"
                    :value="$formatCurrency(props.invoice.shipping_cost)"
                />
                <div class="flex items-center justify-between">
                    <p class="font-bold text-gray-700">
                        {{
                            props.invoice.status !== "paid"
                                ? "Total Tagihan"
                                : "Total"
                        }}
                    </p>
                    <p class="font-bold text-primary">
                        {{ $formatCurrency(total) }}
                    </p>
                </div>

                <!-- Actions -->
                <div v-if="$slots.actions" class="mt-1">
                    <slot name="actions" />
                </div>
            </div>
        </div>
    </DefaultCard>
</template>

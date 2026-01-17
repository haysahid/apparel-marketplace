<script setup lang="ts">
import CopyButton from "@/Components/CopyButton.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import OrderContentRow from "@/Components/OrderContentRow.vue";
import StatusChip from "@/Components/StatusChip.vue";
import Tooltip from "@/Components/Tooltip.vue";
import { ref } from "vue";

const props = defineProps({
    transaction: {
        type: Object as () => TransactionEntity,
        required: true,
    },
    groups: {
        type: Array as () => OrderGroupModel[],
        required: true,
    },
    isLoading: {
        type: Boolean,
        default: false,
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

function copyToClipboard(text: string) {
    navigator.clipboard.writeText(text);

    isCopied.value = true;
    setTimeout(() => {
        isCopied.value = false;
    }, 2000);
}

const isCopied = ref(false);
</script>

<template>
    <DefaultCard isMain class="flex flex-col w-full gap-4 xl:max-w-sm gap-y-3">
        <h3 class="font-semibold text-gray-800">Ringkasan Transaksi</h3>
        <div>
            <div class="flex flex-col gap-2">
                <OrderContentRow
                    v-if="route().current('my-store.transaction.edit')"
                    label="Jenis Transaksi"
                    :value="props.transaction.type?.name"
                />

                <OrderContentRow label="Kode Transaksi">
                    <template #value>
                        <div class="flex items-center gap-0.5">
                            <p
                                class="text-sm font-semibold text-right text-gray-700"
                            >
                                {{ props.transaction.code }}
                            </p>
                            <Tooltip id="copy-code-tooltip" placement="bottom">
                                <template #content>
                                    <p class="text-center min-w-[80px]">
                                        {{
                                            isCopied ? "Disalin!" : "Salin Kode"
                                        }}
                                    </p>
                                </template>

                                <CopyButton
                                    :id="`copy-code-tooltip-transaction-${props.transaction.id}`"
                                    :text="props.transaction.code"
                                />
                            </Tooltip>
                        </div>
                    </template>
                </OrderContentRow>

                <OrderContentRow
                    label="Tgl. Transaksi"
                    :value="$formatDate(props.transaction.created_at)"
                />
                <OrderContentRow
                    label="Status"
                    :value="props.transaction.status"
                    :isLoading="props.isLoading"
                >
                    <template #value>
                        <!-- Status -->
                        <StatusChip
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
                    :value="`- ${$formatCurrency(discount)}`"
                />
                <OrderContentRow
                    label="Biaya Pengiriman"
                    :value="$formatCurrency(props.transaction.shipping_cost)"
                />
                <div class="flex items-center justify-between">
                    <p class="font-bold text-gray-700">
                        {{
                            props.transaction.status !== "paid"
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

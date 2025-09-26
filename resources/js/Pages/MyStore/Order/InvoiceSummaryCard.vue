<script setup lang="ts">
import OrderContentRow from "@/Components/OrderContentRow.vue";
import StatusChip from "@/Components/StatusChip.vue";
import Tooltip from "@/Components/Tooltip.vue";
import { ref } from "vue";

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
    <div
        class="flex flex-col w-full gap-4 p-4 xl:max-w-sm outline outline-1 -outline-offset-1 outline-gray-300 rounded-2xl gap-y-3"
    >
        <h3 class="font-semibold text-gray-700">Ringkasan Pemesanan</h3>
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
                            <Tooltip id="copy-code-tooltip" placement="bottom">
                                <template #content>
                                    <p class="text-center min-w-[80px]">
                                        {{
                                            isCopied ? "Disalin!" : "Salin Kode"
                                        }}
                                    </p>
                                </template>

                                <button
                                    type="button"
                                    @click="copyToClipboard(props.invoice.code)"
                                    class="p-1"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        class="text-gray-500 hover:text-gray-700 size-4"
                                    >
                                        <path
                                            d="M9 9V6.2C9 5.08 9 4.52 9.218 4.092C9.41 3.715 9.715 3.41 10.092 3.218C10.52 3 11.08 3 12.2 3H17.8C18.92 3 19.48 3 19.908 3.218C20.2843 3.40974 20.5903 3.71569 20.782 4.092C21 4.52 21 5.08 21 6.2V11.8C21 12.92 21 13.48 20.782 13.908C20.5903 14.2843 20.2843 14.5903 19.908 14.782C19.48 15 18.92 15 17.803 15H15M9 9H6.2C5.08 9 4.52 9 4.092 9.218C3.71569 9.40974 3.40974 9.71569 3.218 10.092C3 10.52 3 11.08 3 12.2V17.8C3 18.92 3 19.48 3.218 19.908C3.40974 20.2843 3.71569 20.5903 4.092 20.782C4.519 21 5.079 21 6.197 21H11.804C12.921 21 13.48 21 13.908 20.782C14.2843 20.5903 14.5903 20.2843 14.782 19.908C15 19.48 15 18.921 15 17.803V15M9 9H11.8C12.92 9 13.48 9 13.908 9.218C14.2843 9.40974 14.5903 9.71569 14.782 10.092C15 10.519 15 11.079 15 12.197V15"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </button>
                            </Tooltip>
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
    </div>
</template>

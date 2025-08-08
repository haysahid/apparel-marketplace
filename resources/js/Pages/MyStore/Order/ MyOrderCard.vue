<script setup lang="ts">
import AdminItemAction from "@/Components/AdminItemAction.vue";
import OrderStatusChip from "@/Pages/Order/OrderStatusChip.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    invoice: {
        type: Object as () => InvoiceEntity,
        required: true,
    },
});

const emit = defineEmits(["edit"]);
</script>

<template>
    <div
        class="flex flex-col items-center justify-between gap-2 p-2.5 sm:gap-3 sm:p-4 transition-all duration-300 ease-in-out border border-gray-200 rounded-lg hover:border-primary-light hover:ring-1 hover:ring-primary-light relative"
    >
        <div class="flex w-full gap-2">
            <div
                class="flex flex-col items-start justify-start w-full gap-0.5 sm:gap-1"
            >
                <div class="flex items-center gap-2 pe-12">
                    <Link
                        :href="
                            route('my-store.transaction.edit', {
                                transaction: invoice,
                            })
                        "
                        class="text-sm font-medium text-gray-900 md:text-base hover:text-primary"
                    >
                        #{{ invoice.code }}
                    </Link>
                    <OrderStatusChip
                        :status="invoice.transaction.status"
                        :label="invoice.transaction.status.toLocaleUpperCase()"
                        class="w-fit"
                    />
                </div>
                <div class="flex items-end justify-between w-full gap-2">
                    <div>
                        <p class="text-xs text-gray-500">
                            {{ props.invoice.transaction.user.name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ props.invoice.transaction.items.length }} items
                        </p>
                        <p class="text-xs text-gray-500">
                            <span>
                                {{
                                    props.invoice.transaction.payment_method
                                        .name
                                }}
                            </span>
                            <span class="mx-1">•</span>
                            <span>
                                {{
                                    props.invoice.transaction.shipping_method
                                        .name
                                }}
                            </span>
                        </p>
                    </div>
                    <p class="text-sm font-medium text-gray-600">
                        {{ $formatCurrency(invoice.amount) }}
                    </p>
                </div>
            </div>
        </div>

        <AdminItemAction
            class="absolute top-2.5 right-2.5 sm:top-4 sm:right-4"
            @edit="emit('edit')"
        />
    </div>
</template>

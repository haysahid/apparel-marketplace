<script setup lang="ts">
import AdminItemAction from "@/Components/AdminItemAction.vue";
import OrderStatusChip from "@/Pages/Order/OrderStatusChip.vue";
import { Link } from "@inertiajs/vue3";
import { computed, getCurrentInstance } from "vue";

const props = defineProps({
    payment: {
        type: Object as () => PaymentEntity,
        required: true,
    },
});

const emit = defineEmits(["edit"]);

const hasEditCallback = computed(() => {
    return !!getCurrentInstance()?.vnode?.props?.["onEdit"];
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-between gap-2 p-2.5 sm:gap-3 sm:p-4 transition-all duration-300 ease-in-out border border-gray-200 rounded-lg hover:border-primary-light hover:ring-1 hover:ring-primary-light relative"
    >
        <div class="flex w-full gap-2">
            <div
                class="flex flex-col items-start justify-start w-full gap-0.5 sm:gap-1"
            >
                <div class="flex flex-col gap-0.5">
                    <div class="flex items-center gap-2 pe-12">
                        <Link
                            :href="
                                route('my-store.transaction.edit', {
                                    transaction: payment.transaction,
                                })
                            "
                            class="text-sm font-medium text-gray-900 transition-all duration-300 ease-in-out md:text-base hover:text-primary"
                        >
                            {{ payment.transaction.code }}
                        </Link>
                        <OrderStatusChip
                            :status="payment.status"
                            :label="payment.status.toLocaleUpperCase()"
                            class="w-fit"
                        />
                    </div>
                    <p class="text-xs text-gray-500">
                        {{ $formatDate(payment.transaction.created_at) }}
                    </p>
                </div>
                <div class="flex items-end justify-between w-full gap-2">
                    <div
                        class="flex flex-wrap text-xs text-gray-600 gap-x-6 gap-y-0.5"
                    >
                        <div class="flex items-center gap-0.5">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                class="inline-block size-3.5 mr-1 fill-gray-400"
                            >
                                <path
                                    d="M12 12C10.9 12 9.95833 11.6083 9.175 10.825C8.39167 10.0417 8 9.1 8 8C8 6.9 8.39167 5.95833 9.175 5.175C9.95833 4.39167 10.9 4 12 4C13.1 4 14.0417 4.39167 14.825 5.175C15.6083 5.95833 16 6.9 16 8C16 9.1 15.6083 10.0417 14.825 10.825C14.0417 11.6083 13.1 12 12 12ZM4 18V17.2C4 16.6333 4.146 16.1127 4.438 15.638C4.73 15.1633 5.11733 14.8007 5.6 14.55C6.63333 14.0333 7.68333 13.646 8.75 13.388C9.81667 13.13 10.9 13.0007 12 13C13.1 12.9993 14.1833 13.1287 15.25 13.388C16.3167 13.6473 17.3667 14.0347 18.4 14.55C18.8833 14.8 19.271 15.1627 19.563 15.638C19.855 16.1133 20.0007 16.634 20 17.2V18C20 18.55 19.8043 19.021 19.413 19.413C19.0217 19.805 18.5507 20.0007 18 20H6C5.45 20 4.97933 19.8043 4.588 19.413C4.19667 19.0217 4.00067 18.5507 4 18Z"
                                />
                            </svg>
                            <span>
                                {{ props.payment.transaction.user.name }}
                            </span>
                        </div>
                        <div class="flex items-center gap-0.5">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                class="inline-block size-3.5 mr-1 fill-gray-400"
                            >
                                <path
                                    d="M5.25 5C4.38805 5 3.5614 5.34241 2.9519 5.9519C2.34241 6.5614 2 7.38805 2 8.25V9.5H22V8.25C22 7.8232 21.9159 7.40059 21.7526 7.00628C21.5893 6.61197 21.3499 6.25369 21.0481 5.9519C20.7463 5.65011 20.388 5.41072 19.9937 5.24739C19.5994 5.08406 19.1768 5 18.75 5H5.25ZM22 11H2V15.75C2 16.612 2.34241 17.4386 2.9519 18.0481C3.5614 18.6576 4.38805 19 5.25 19H18.75C19.1768 19 19.5994 18.9159 19.9937 18.7526C20.388 18.5893 20.7463 18.3499 21.0481 18.0481C21.3499 17.7463 21.5893 17.388 21.7526 16.9937C21.9159 16.5994 22 16.1768 22 15.75V11ZM15.75 14.5H18.25C18.4489 14.5 18.6397 14.579 18.7803 14.7197C18.921 14.8603 19 15.0511 19 15.25C19 15.4489 18.921 15.6397 18.7803 15.7803C18.6397 15.921 18.4489 16 18.25 16H15.75C15.5511 16 15.3603 15.921 15.2197 15.7803C15.079 15.6397 15 15.4489 15 15.25C15 15.0511 15.079 14.8603 15.2197 14.7197C15.3603 14.579 15.5511 14.5 15.75 14.5Z"
                                />
                            </svg>
                            <span>
                                {{ props.payment.payment_method.name }}
                            </span>
                        </div>
                        <div
                            v-if="
                                payment.status === 'completed' &&
                                payment.transaction.paid_at
                            "
                            class="flex items-center gap-0.5"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                class="inline-block size-3.5 mr-1 fill-gray-400"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M12 21C13.1819 21 14.3522 20.7672 15.4442 20.3149C16.5361 19.8626 17.5282 19.1997 18.364 18.364C19.1997 17.5282 19.8626 16.5361 20.3149 15.4442C20.7672 14.3522 21 13.1819 21 12C21 10.8181 20.7672 9.64778 20.3149 8.55585C19.8626 7.46392 19.1997 6.47177 18.364 5.63604C17.5282 4.80031 16.5361 4.13738 15.4442 3.68508C14.3522 3.23279 13.1819 3 12 3C9.61305 3 7.32387 3.94821 5.63604 5.63604C3.94821 7.32387 3 9.61305 3 12C3 14.3869 3.94821 16.6761 5.63604 18.364C7.32387 20.0518 9.61305 21 12 21ZM11.768 15.64L16.768 9.64L15.232 8.36L10.932 13.519L8.707 11.293L7.293 12.707L10.293 15.707L11.067 16.481L11.768 15.64Z"
                                />
                            </svg>
                            <span>
                                {{
                                    payment.midtrans_response?.va_numbers[0]?.bank.toUpperCase()
                                }}
                                -
                                {{ $formatDate(payment.transaction.paid_at) }}
                            </span>
                        </div>
                    </div>
                    <p class="text-sm font-medium text-gray-700">
                        {{
                            $formatCurrency(
                                payment.midtrans_response?.gross_amount
                            )
                        }}
                    </p>
                </div>
            </div>
        </div>

        <AdminItemAction
            v-if="hasEditCallback"
            class="absolute top-2.5 right-2.5 sm:top-4 sm:right-4"
            @edit="emit('edit')"
        />
    </div>
</template>

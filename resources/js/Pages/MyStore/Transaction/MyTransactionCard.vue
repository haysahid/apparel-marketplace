<script setup lang="ts">
import AdminItemAction from "@/Components/AdminItemAction.vue";
import OrderStatusChip from "@/Pages/Order/OrderStatusChip.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    transaction: {
        type: Object as () => TransactionEntity,
        required: true,
    },
});

const emit = defineEmits(["edit"]);
</script>

<template>
    <Link
        :href="
            route('my-store.transaction.edit', {
                transaction: transaction,
            })
        "
        class="flex flex-col items-center justify-between gap-2 p-2.5 sm:gap-3 sm:p-4 transition-all duration-300 ease-in-out border border-gray-200 rounded-lg hover:border-primary-light hover:ring-1 hover:ring-primary-light relative group"
    >
        <div class="flex w-full gap-2">
            <div
                class="flex flex-col items-start justify-start w-full gap-0.5 sm:gap-1"
            >
                <div class="flex flex-col gap-0.5">
                    <div class="flex items-center gap-2 pe-12">
                        <p
                            class="text-sm font-medium text-gray-900 transition-all duration-300 ease-in-out md:text-base group-hover:text-primary"
                        >
                            {{ transaction.code }}
                        </p>
                        <OrderStatusChip
                            :status="transaction.status"
                            :label="transaction.status.toLocaleUpperCase()"
                            class="w-fit"
                        />
                    </div>
                    <p class="text-xs text-gray-500">
                        {{ transaction.type.name }} -
                        {{ $formatDate(props.transaction.created_at) }}
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
                                {{ props.transaction.user.name }}
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
                                    d="M20.7651 7.98189L20.7871 8.17189L20.7941 8.36589V15.6339C20.794 16.0425 20.6937 16.4448 20.5021 16.8057C20.3106 17.1665 20.0334 17.4749 19.6951 17.7039L19.5451 17.7989L13.2501 21.4329L13.1261 21.4999L13.0001 21.5599V12.5699L20.7651 7.98189ZM3.23505 7.98189L11.0001 12.5709V21.5589C10.9148 21.5208 10.8314 21.4788 10.7501 21.4329L4.45605 17.7989C4.07602 17.5795 3.76043 17.2639 3.54101 16.8839C3.32159 16.5038 3.20607 16.0727 3.20605 15.6339V8.36589C3.20605 8.23589 3.21605 8.10789 3.23605 7.98189H3.23505ZM13.2501 2.56689L19.5441 6.20089C19.5947 6.23089 19.6441 6.26156 19.6921 6.29289L12.0001 10.8399L4.30805 6.29189C4.35649 6.2598 4.40584 6.22912 4.45605 6.19989L10.7501 2.56589C11.1301 2.34647 11.5612 2.23096 12.0001 2.23096C12.4389 2.23096 12.87 2.34747 13.2501 2.56689Z"
                                />
                            </svg>
                            <span>
                                {{ props.transaction.items.length }}
                                items
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
                                {{ props.transaction.payment_method.name }}
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
                                    d="M6 20C5.16667 20 4.45833 19.7083 3.875 19.125C3.29167 18.5417 3 17.8333 3 17H1V6C1 5.45 1.196 4.97933 1.588 4.588C1.98 4.19667 2.45067 4.00067 3 4H17V8H20L23 12V17H21C21 17.8333 20.7083 18.5417 20.125 19.125C19.5417 19.7083 18.8333 20 18 20C17.1667 20 16.4583 19.7083 15.875 19.125C15.2917 18.5417 15 17.8333 15 17H9C9 17.8333 8.70833 18.5417 8.125 19.125C7.54167 19.7083 6.83333 20 6 20ZM6 18C6.28333 18 6.521 17.904 6.713 17.712C6.905 17.52 7.00067 17.2827 7 17C6.99933 16.7173 6.90333 16.48 6.712 16.288C6.52067 16.096 6.28333 16 6 16C5.71667 16 5.47933 16.096 5.288 16.288C5.09667 16.48 5.00067 16.7173 5 17C4.99933 17.2827 5.09533 17.5203 5.288 17.713C5.48067 17.9057 5.718 18.0013 6 18ZM18 18C18.2833 18 18.521 17.904 18.713 17.712C18.905 17.52 19.0007 17.2827 19 17C18.9993 16.7173 18.9033 16.48 18.712 16.288C18.5207 16.096 18.2833 16 18 16C17.7167 16 17.4793 16.096 17.288 16.288C17.0967 16.48 17.0007 16.7173 17 17C16.9993 17.2827 17.0953 17.5203 17.288 17.713C17.4807 17.9057 17.718 18.0013 18 18ZM17 13H21.25L19 10H17V13Z"
                                />
                            </svg>
                            <span>
                                {{ props.transaction.shipping_method.name }}
                            </span>
                        </div>
                    </div>
                    <p class="text-sm font-medium text-gray-700">
                        {{
                            $formatCurrency(
                                props.transaction.items.reduce(
                                    (total, item) => total + item.subtotal,
                                    0
                                )
                            )
                        }}
                    </p>
                </div>
            </div>
        </div>

        <AdminItemAction
            class="absolute top-2.5 right-2.5 sm:top-4 sm:right-4"
            @edit="emit('edit')"
        />
    </Link>
</template>

<script setup lang="ts">
import AdminItemAction from "@/Components/AdminItemAction.vue";
import MembershipBadge from "@/Components/MembershipBadge.vue";
import MembershipBadgeSquare from "@/Components/MembershipBadgeSquare.vue";
import { getImageUrl } from "@/plugins/helpers";
import { computed, getCurrentInstance } from "vue";

const props = defineProps({
    membership: {
        required: true,
        type: Object as () => MembershipEntity,
    },
    hideEditButton: {
        type: Boolean,
        default: false,
    },
    hideDeleteButton: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["edit", "delete"]);

const hasEditCallback = computed(() => {
    return !!getCurrentInstance()?.vnode?.props?.["onEdit"];
});
const hasDeleteCallback = computed(() => {
    return !!getCurrentInstance()?.vnode?.props?.["onDelete"];
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-between gap-2 p-2.5 sm:gap-3 sm:p-4 transition-all duration-300 ease-in-out border border-gray-200 rounded-lg hover:border-primary-light hover:ring-1 hover:ring-primary-light relative"
    >
        <div class="flex w-full gap-2.5 sm:gap-3 items-center">
            <MembershipBadgeSquare :membership="props.membership" />

            <div class="flex flex-col items-start justify-center gap-1">
                <div class="flex flex-col">
                    <p class="text-sm font-medium text-gray-700">
                        <span>{{ props.membership.name }}</span>
                        <span
                            v-if="props.membership.alias"
                            class="text-xs italic text-gray-500"
                        >
                            - {{ props.membership.alias }}
                        </span>
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ props.membership.group }}
                    </p>
                </div>

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
                                d="M20.7651 7.98189L20.7871 8.17189L20.7941 8.36589V15.6339C20.794 16.0425 20.6937 16.4448 20.5021 16.8057C20.3106 17.1665 20.0334 17.4749 19.6951 17.7039L19.5451 17.7989L13.2501 21.4329L13.1261 21.4999L13.0001 21.5599V12.5699L20.7651 7.98189ZM3.23505 7.98189L11.0001 12.5709V21.5589C10.9148 21.5208 10.8314 21.4788 10.7501 21.4329L4.45605 17.7989C4.07602 17.5795 3.76043 17.2639 3.54101 16.8839C3.32159 16.5038 3.20607 16.0727 3.20605 15.6339V8.36589C3.20605 8.23589 3.21605 8.10789 3.23605 7.98189H3.23505ZM13.2501 2.56689L19.5441 6.20089C19.5947 6.23089 19.6441 6.26156 19.6921 6.29289L12.0001 10.8399L4.30805 6.29189C4.35649 6.2598 4.40584 6.22912 4.45605 6.19989L10.7501 2.56589C11.1301 2.34647 11.5612 2.23096 12.0001 2.23096C12.4389 2.23096 12.87 2.34747 13.2501 2.56689Z"
                            />
                        </svg>
                        <span>
                            {{ props.membership.item_discount_percentage }}%
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
                            {{ props.membership.shipping_discount_percentage }}%
                        </span>
                    </div>

                    <p>
                        Minimal Pembelian
                        {{
                            $formatCurrency(
                                props.membership.min_purchase_amount
                            )
                        }}
                    </p>
                </div>
            </div>
        </div>

        <AdminItemAction
            v-if="
                (hasEditCallback && !props.hideEditButton) ||
                (hasDeleteCallback && !props.hideDeleteButton)
            "
            class="absolute top-2.5 right-2.5 sm:top-4 sm:right-4"
            :hideEditButton="props.hideEditButton"
            :hideDeleteButton="props.hideDeleteButton"
            @edit="emit('edit')"
            @delete="emit('delete')"
        />
    </div>
</template>

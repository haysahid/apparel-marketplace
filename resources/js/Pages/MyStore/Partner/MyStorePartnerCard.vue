<script setup lang="ts">
import AdminItemAction from "@/Components/AdminItemAction.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    partner: {
        type: Object as () => PartnerEntity,
        required: true,
    },
});

const emit = defineEmits(["edit", "delete"]);
</script>

<template>
    <div
        class="flex items-center justify-between gap-2.5 p-2.5 sm:gap-3 sm:p-4 transition-all duration-300 ease-in-out border border-gray-200 rounded-lg hover:border-primary-light hover:ring-1 hover:ring-primary-light"
    >
        <img
            v-if="props.partner.logo"
            :src="$getImageUrl(props.partner.logo)"
            alt="Brand Logo"
            class="object-contain w-[80px] sm:w-[100px] rounded aspect-[3/2]"
        />
        <div
            v-else
            class="flex items-center justify-center min-w-[80px] sm:min-w-[100px] bg-gray-100 rounded aspect-[3/2]"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                class="size-6 fill-gray-400"
            >
                <path
                    d="M5 21C4.45 21 3.97933 20.8043 3.588 20.413C3.19667 20.0217 3.00067 19.5507 3 19V5C3 4.45 3.196 3.97933 3.588 3.588C3.98 3.19667 4.45067 3.00067 5 3H19C19.55 3 20.021 3.196 20.413 3.588C20.805 3.98 21.0007 4.45067 21 5V19C21 19.55 20.8043 20.021 20.413 20.413C20.0217 20.805 19.5507 21.0007 19 21H5ZM6 17H18L14.25 12L11.25 16L9 13L6 17Z"
                />
            </svg>
        </div>
        <div class="flex flex-col items-start w-full gap-1">
            <Link
                :href="
                    route('my-store.partner.show', {
                        partner: props.partner,
                    })
                "
                class="text-sm font-medium text-gray-900 hover:text-primary"
            >
                {{ props.partner.name }}
            </Link>
            <p class="text-xs text-gray-500 line-clamp-2 overflow-ellipsis">
                {{ props.partner.description }}
            </p>
            <div class="flex flex-wrap text-xs text-gray-600 gap-x-6 gap-y-0.5">
                <div class="flex items-center gap-0.5">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        class="inline-block size-3.5 mr-1 fill-gray-400 shrink-0"
                    >
                        <path
                            d="M12 12C10.9 12 9.95833 11.6083 9.175 10.825C8.39167 10.0417 8 9.1 8 8C8 6.9 8.39167 5.95833 9.175 5.175C9.95833 4.39167 10.9 4 12 4C13.1 4 14.0417 4.39167 14.825 5.175C15.6083 5.95833 16 6.9 16 8C16 9.1 15.6083 10.0417 14.825 10.825C14.0417 11.6083 13.1 12 12 12ZM4 18V17.2C4 16.6333 4.146 16.1127 4.438 15.638C4.73 15.1633 5.11733 14.8007 5.6 14.55C6.63333 14.0333 7.68333 13.646 8.75 13.388C9.81667 13.13 10.9 13.0007 12 13C13.1 12.9993 14.1833 13.1287 15.25 13.388C16.3167 13.6473 17.3667 14.0347 18.4 14.55C18.8833 14.8 19.271 15.1627 19.563 15.638C19.855 16.1133 20.0007 16.634 20 17.2V18C20 18.55 19.8043 19.021 19.413 19.413C19.0217 19.805 18.5507 20.0007 18 20H6C5.45 20 4.97933 19.8043 4.588 19.413C4.19667 19.0217 4.00067 18.5507 4 18Z"
                        />
                    </svg>
                    <span>
                        {{ props.partner.contact_name }}
                    </span>
                </div>
                <div class="flex items-center gap-0.5">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        class="inline-block size-3.5 mr-1 fill-gray-400 shrink-0"
                    >
                        <path
                            d="M4 20C3.45 20 2.97933 19.8043 2.588 19.413C2.19667 19.0217 2.00067 18.5507 2 18V6C2 5.45 2.196 4.97933 2.588 4.588C2.98 4.19667 3.45067 4.00067 4 4H20C20.55 4 21.021 4.196 21.413 4.588C21.805 4.98 22.0007 5.45067 22 6V18C22 18.55 21.8043 19.021 21.413 19.413C21.0217 19.805 20.5507 20.0007 20 20H4ZM12 12.825C12.0833 12.825 12.171 12.8123 12.263 12.787C12.355 12.7617 12.4423 12.7243 12.525 12.675L19.6 8.25C19.7333 8.16667 19.8333 8.06267 19.9 7.938C19.9667 7.81333 20 7.67567 20 7.525C20 7.19167 19.8583 6.94167 19.575 6.775C19.2917 6.60833 19 6.61667 18.7 6.8L12 11L5.3 6.8C5 6.61667 4.70833 6.61267 4.425 6.788C4.14167 6.96333 4 7.209 4 7.525C4 7.69167 4.03333 7.83767 4.1 7.963C4.16667 8.08833 4.26667 8.184 4.4 8.25L11.475 12.675C11.5583 12.725 11.646 12.7627 11.738 12.788C11.83 12.8133 11.9173 12.8257 12 12.825Z"
                        />
                    </svg>
                    <span>
                        {{ props.partner.contact_email }}
                    </span>
                </div>
                <div
                    v-if="props.partner.contact_phone"
                    class="flex items-center gap-0.5"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        class="inline-block size-3.5 mr-1 fill-gray-400 shrink-0"
                    >
                        <path
                            d="M20.4868 17.14L16.4218 13.444C16.2298 13.2691 15.9772 13.1758 15.7176 13.1838C15.458 13.1919 15.2117 13.3006 15.0308 13.487L12.6378 15.948C12.0618 15.838 10.9038 15.477 9.71179 14.288C8.51979 13.095 8.15879 11.934 8.05179 11.362L10.5108 8.968C10.6972 8.78712 10.8059 8.54082 10.814 8.2812C10.822 8.02159 10.7287 7.76904 10.5538 7.57699L6.85879 3.51299C6.68384 3.32035 6.44067 3.2035 6.18095 3.18725C5.92122 3.17101 5.66539 3.25665 5.46779 3.42599L3.29779 5.28699C3.1249 5.46051 3.02171 5.69145 3.00779 5.93599C2.99279 6.18599 2.70679 12.108 7.29879 16.702C11.3048 20.707 16.3228 21 17.7048 21C17.9068 21 18.0308 20.994 18.0638 20.992C18.3081 20.9776 18.5387 20.874 18.7118 20.701L20.5718 18.53C20.7418 18.333 20.8281 18.0774 20.8122 17.8177C20.7963 17.558 20.6795 17.3148 20.4868 17.14Z"
                        />
                    </svg>
                    <span>
                        {{ props.partner.contact_phone }}
                    </span>
                </div>
                <div
                    v-if="props.partner.address"
                    class="flex items-center gap-0.5"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        class="inline-block size-3.5 mr-1 fill-gray-400 shrink-0"
                    >
                        <path
                            d="M12 2C16.6945 2 20.5 5.8055 20.5 10.5C20.5033 12.5106 19.7908 14.4568 18.49 15.99L18.489 15.9905L18.481 16H18.5L13.4555 21.3545C13.2685 21.5529 13.043 21.711 12.7927 21.819C12.5424 21.9271 12.2726 21.9828 12 21.9828C11.7274 21.9828 11.4576 21.9271 11.2074 21.819C10.9571 21.711 10.7315 21.5529 10.5445 21.3545L5.50002 16H5.51902L5.51002 15.99L5.50002 15.9775C5.17673 15.5946 4.88793 15.1838 4.63702 14.75C3.8894 13.4586 3.49711 11.9922 3.50002 10.5C3.50002 5.8055 7.30552 2 12 2ZM12 7.5C11.2044 7.5 10.4413 7.81607 9.8787 8.37868C9.31609 8.94129 9.00002 9.70435 9.00002 10.5C9.00002 11.2956 9.31609 12.0587 9.8787 12.6213C10.4413 13.1839 11.2044 13.5 12 13.5C12.7957 13.5 13.5587 13.1839 14.1213 12.6213C14.6839 12.0587 15 11.2956 15 10.5C15 9.70435 14.6839 8.94129 14.1213 8.37868C13.5587 7.81607 12.7957 7.5 12 7.5Z"
                        />
                    </svg>
                    <span>
                        {{ props.partner.address }}
                    </span>
                </div>
            </div>
        </div>

        <AdminItemAction class="self-start">
            <template #moreContent>
                <div class="divide-y divide-gray-200">
                    <DropdownLink as="button" @click="emit('edit')">
                        Ubah
                    </DropdownLink>
                    <DropdownLink as="button" @click="emit('delete')">
                        Hapus
                    </DropdownLink>
                </div>
            </template>
        </AdminItemAction>
    </div>
</template>

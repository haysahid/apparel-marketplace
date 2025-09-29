<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    customer: {
        type: Object as () => UserEntity,
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
            <img
                v-if="customer.avatar"
                :src="customer.avatar"
                alt="Foto Pelanggan"
                class="object-contain rounded-full size-10 h-fit shrink-0"
            />
            <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                width="44"
                height="44"
                viewBox="0 0 44 44"
                class="size-10 h-fit fill-gray-400 shrink-0"
            >
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M40.3333 22.0003C40.3333 32.1258 32.1255 40.3337 22 40.3337C11.8745 40.3337 3.66663 32.1258 3.66663 22.0003C3.66663 11.8748 11.8745 3.66699 22 3.66699C32.1255 3.66699 40.3333 11.8748 40.3333 22.0003ZM27.5 16.5003C27.5 17.959 26.9205 19.358 25.889 20.3894C24.8576 21.4209 23.4586 22.0003 22 22.0003C20.5413 22.0003 19.1423 21.4209 18.1109 20.3894C17.0794 19.358 16.5 17.959 16.5 16.5003C16.5 15.0416 17.0794 13.6427 18.1109 12.6112C19.1423 11.5798 20.5413 11.0003 22 11.0003C23.4586 11.0003 24.8576 11.5798 25.889 12.6112C26.9205 13.6427 27.5 15.0416 27.5 16.5003ZM22 37.5837C25.1465 37.5887 28.2201 36.6366 30.8128 34.8538C31.9201 34.093 32.3931 32.6447 31.7478 31.4658C30.415 29.022 27.665 27.5003 22 27.5003C16.335 27.5003 13.585 29.022 12.2503 31.4658C11.6068 32.6447 12.0798 34.093 13.1871 34.8538C15.7798 36.6366 18.8535 37.5887 22 37.5837Z"
                />
            </svg>

            <div
                class="flex flex-col items-start justify-start w-full gap-0.5 sm:gap-1"
            >
                <div>
                    <div class="flex items-center gap-2 pe-12">
                        <Link
                            :href="
                                route('my-store.customer.show', {
                                    customer: customer,
                                })
                            "
                            class="text-sm font-medium text-gray-900 md:text-base hover:text-primary"
                        >
                            {{ customer.name }}
                        </Link>
                    </div>
                </div>
                <div
                    class="flex flex-wrap text-xs text-gray-600 gap-x-6 gap-y-0.5"
                >
                    <div
                        v-if="props.customer.email"
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
                                d="M4 20C3.45 20 2.97933 19.8043 2.588 19.413C2.19667 19.0217 2.00067 18.5507 2 18V6C2 5.45 2.196 4.97933 2.588 4.588C2.98 4.19667 3.45067 4.00067 4 4H20C20.55 4 21.021 4.196 21.413 4.588C21.805 4.98 22.0007 5.45067 22 6V18C22 18.55 21.8043 19.021 21.413 19.413C21.0217 19.805 20.5507 20.0007 20 20H4ZM12 12.825C12.0833 12.825 12.171 12.8123 12.263 12.787C12.355 12.7617 12.4423 12.7243 12.525 12.675L19.6 8.25C19.7333 8.16667 19.8333 8.06267 19.9 7.938C19.9667 7.81333 20 7.67567 20 7.525C20 7.19167 19.8583 6.94167 19.575 6.775C19.2917 6.60833 19 6.61667 18.7 6.8L12 11L5.3 6.8C5 6.61667 4.70833 6.61267 4.425 6.788C4.14167 6.96333 4 7.209 4 7.525C4 7.69167 4.03333 7.83767 4.1 7.963C4.16667 8.08833 4.26667 8.184 4.4 8.25L11.475 12.675C11.5583 12.725 11.646 12.7627 11.738 12.788C11.83 12.8133 11.9173 12.8257 12 12.825Z"
                            />
                        </svg>
                        <span>
                            {{ props.customer.email }}
                        </span>
                    </div>
                    <div
                        v-if="props.customer.phone"
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
                                d="M20.4868 17.14L16.4218 13.444C16.2298 13.2691 15.9772 13.1758 15.7176 13.1838C15.458 13.1919 15.2117 13.3006 15.0308 13.487L12.6378 15.948C12.0618 15.838 10.9038 15.477 9.71179 14.288C8.51979 13.095 8.15879 11.934 8.05179 11.362L10.5108 8.968C10.6972 8.78712 10.8059 8.54082 10.814 8.2812C10.822 8.02159 10.7287 7.76904 10.5538 7.57699L6.85879 3.51299C6.68384 3.32035 6.44067 3.2035 6.18095 3.18725C5.92122 3.17101 5.66539 3.25665 5.46779 3.42599L3.29779 5.28699C3.1249 5.46051 3.02171 5.69145 3.00779 5.93599C2.99279 6.18599 2.70679 12.108 7.29879 16.702C11.3048 20.707 16.3228 21 17.7048 21C17.9068 21 18.0308 20.994 18.0638 20.992C18.3081 20.9776 18.5387 20.874 18.7118 20.701L20.5718 18.53C20.7418 18.333 20.8281 18.0774 20.8122 17.8177C20.7963 17.558 20.6795 17.3148 20.4868 17.14Z"
                            />
                        </svg>
                        <span>
                            {{ props.customer.phone }}
                        </span>
                    </div>
                    <div
                        v-if="props.customer.store_roles?.length"
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
                                d="M20 4H4C2.897 4 2 4.897 2 6V18C2 19.103 2.897 20 4 20H20C21.103 20 22 19.103 22 18V6C22 4.897 21.103 4 20 4ZM8.715 8C9.866 8 10.715 8.849 10.715 10C10.715 11.151 9.866 12 8.715 12C7.564 12 6.715 11.151 6.715 10C6.715 8.849 7.563 8 8.715 8ZM12.43 16H5V15.535C5 14.162 6.676 12.75 8.715 12.75C10.754 12.75 12.43 14.162 12.43 15.535V16ZM19 15H15V13H19V15ZM19 11H14V9H19V11Z"
                            />
                        </svg>
                        <span>
                            {{
                                props.customer.store_roles
                                    ?.map((role) => role.name)
                                    ?.join(", ") || "-"
                            }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- <AdminItemAction
            class="absolute top-2.5 right-2.5 sm:top-4 sm:right-4"
            @edit="emit('edit')"
        /> -->
    </div>
</template>

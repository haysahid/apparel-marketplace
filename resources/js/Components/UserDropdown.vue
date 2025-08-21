<script setup>
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    invert: {
        type: Boolean,
        default: false,
    },
    responsive: {
        type: Boolean,
        default: false,
    },
});

const logout = () => {
    localStorage.removeItem("access_token");
    localStorage.removeItem("selected_store_id");
    router.post(route("logout"));
};
</script>

<template>
    <Dropdown align="right" width="48">
        <template #trigger>
            <div
                class="flex rounded-md"
                :class="{
                    'px-1': props.responsive,
                }"
            >
                <button
                    type="button"
                    class="flex items-center justify-start w-full gap-2 px-4 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out bg-transparent border border-transparent rounded-none sm:rounded-md sm:px-3 text-start sm:w-auto text-gray-500/90 hover:text-gray-500 focus:outline-none hover:bg-gray-500/10 focus:bg-gray-500/10 active:bg-gray-500/20 group"
                    :class="{
                        '!text-white/80 hover:bg-white/10 focus:bg-white/10':
                            props.invert,
                    }"
                >
                    <img
                        v-if="$page.props.auth.user.avatar"
                        class="object-cover rounded-full size-8"
                        :src="$page.props.auth.user.avatar"
                        :alt="$page.props.auth.user.name"
                    />
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        width="44"
                        height="44"
                        viewBox="0 0 44 44"
                        class="transition duration-150 ease-in-out fill-gray-400 size-8"
                        :class="{
                            'fill-white/80 group-hover:fill-white/80 group-focus:fill-white/80':
                                props.invert,
                        }"
                    >
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M40.3333 22.0003C40.3333 32.1258 32.1255 40.3337 22 40.3337C11.8745 40.3337 3.66663 32.1258 3.66663 22.0003C3.66663 11.8748 11.8745 3.66699 22 3.66699C32.1255 3.66699 40.3333 11.8748 40.3333 22.0003ZM27.5 16.5003C27.5 17.959 26.9205 19.358 25.889 20.3894C24.8576 21.4209 23.4586 22.0003 22 22.0003C20.5413 22.0003 19.1423 21.4209 18.1109 20.3894C17.0794 19.358 16.5 17.959 16.5 16.5003C16.5 15.0416 17.0794 13.6427 18.1109 12.6112C19.1423 11.5798 20.5413 11.0003 22 11.0003C23.4586 11.0003 24.8576 11.5798 25.889 12.6112C26.9205 13.6427 27.5 15.0416 27.5 16.5003ZM22 37.5837C25.1465 37.5887 28.2201 36.6366 30.8128 34.8538C31.9201 34.093 32.3931 32.6447 31.7478 31.4658C30.415 29.022 27.665 27.5003 22 27.5003C16.335 27.5003 13.585 29.022 12.2503 31.4658C11.6068 32.6447 12.0798 34.093 13.1871 34.8538C15.7798 36.6366 18.8535 37.5887 22 37.5837Z"
                        />
                    </svg>

                    <span class="w-full sm:w-auto sm:hidden md:inline">
                        {{ $page.props.auth.user.name }}
                    </span>

                    <svg
                        class="size-4 sm:hidden md:inline"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                        />
                    </svg>
                </button>
            </div>
        </template>

        <template #content>
            <div class="divide-y divide-gray-200">
                <DropdownLink
                    v-if="$page.props.auth.has_store"
                    as="button"
                    @click="$emit('showStoreOptionsDialog')"
                >
                    Toko Saya
                </DropdownLink>

                <DropdownLink v-else :href="route('store.create')">
                    Buat Toko
                </DropdownLink>

                <DropdownLink :href="route('profile')"> Profile </DropdownLink>

                <DropdownLink as="button" @click="logout">
                    Log Out
                </DropdownLink>
            </div>
        </template>
    </Dropdown>
</template>

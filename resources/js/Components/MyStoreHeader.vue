<script setup>
import { defineProps, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import AdminSidebar from "./AdminSidebar.vue";

const props = defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const logout = () => {
    localStorage.removeItem("access_token");
    router.post(route("logout"));
};
</script>

<template>
    <nav
        class="bg-white z-50 py-1 h-auto sm:h-[72px] fixed top-0 md:w-[calc(100vw-256px)] w-full border-b border-gray-200"
        :class="{
            '!h-auto': showingNavigationDropdown,
        }"
    >
        <!-- Primary Navigation Menu -->
        <div
            class="px-4 mx-auto transition-all duration-300 ease-in-out sm:px-6 lg:px-8"
        >
            <div class="flex items-center justify-between h-16">
                <Link :href="route('home')" class="block md:hidden">
                    <img
                        src="/storage/logo_black.png"
                        alt="Logo"
                        class="w-auto h-12 sm:h-16"
                    />
                </Link>

                <slot name="leading" />

                <div class="hidden md:flex sm:items-center sm:ms-6">
                    <!-- Settings Dropdown -->
                    <div class="relative ms-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button
                                        type="button"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out border border-transparent rounded-md bg-secondary hover:text-gray-700 focus:outline-none focus:bg-secondary/80 active:bg-secondary/90"
                                    >
                                        <img
                                            v-if="$page.props.auth.user.avatar"
                                            class="object-cover rounded-full size-8 me-2"
                                            :src="$page.props.auth.user.avatar"
                                            :alt="$page.props.auth.user.name"
                                        />
                                        <svg
                                            v-else
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="44"
                                            height="44"
                                            viewBox="0 0 44 44"
                                            class="fill-primary size-8 me-1.5"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                clip-rule="evenodd"
                                                d="M40.3333 22.0003C40.3333 32.1258 32.1255 40.3337 22 40.3337C11.8745 40.3337 3.66663 32.1258 3.66663 22.0003C3.66663 11.8748 11.8745 3.66699 22 3.66699C32.1255 3.66699 40.3333 11.8748 40.3333 22.0003ZM27.5 16.5003C27.5 17.959 26.9205 19.358 25.889 20.3894C24.8576 21.4209 23.4586 22.0003 22 22.0003C20.5413 22.0003 19.1423 21.4209 18.1109 20.3894C17.0794 19.358 16.5 17.959 16.5 16.5003C16.5 15.0416 17.0794 13.6427 18.1109 12.6112C19.1423 11.5798 20.5413 11.0003 22 11.0003C23.4586 11.0003 24.8576 11.5798 25.889 12.6112C26.9205 13.6427 27.5 15.0416 27.5 16.5003ZM22 37.5837C25.1465 37.5887 28.2201 36.6366 30.8128 34.8538C31.9201 34.093 32.3931 32.6447 31.7478 31.4658C30.415 29.022 27.665 27.5003 22 27.5003C16.335 27.5003 13.585 29.022 12.2503 31.4658C11.6068 32.6447 12.0798 34.093 13.1871 34.8538C15.7798 36.6366 18.8535 37.5887 22 37.5837Z"
                                            />
                                        </svg>

                                        {{ $page.props.auth.user.name }}

                                        <svg
                                            class="ms-2 -me-0.5 size-4"
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
                                </span>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile')">
                                    Profile
                                </DropdownLink>

                                <div class="border-t border-gray-200" />

                                <!-- Authentication -->
                                <form @submit.prevent="logout">
                                    <DropdownLink as="button">
                                        Log Out
                                    </DropdownLink>
                                </form>
                            </template>
                        </Dropdown>
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="flex items-center -me-2 md:hidden">
                    <button
                        class="inline-flex items-center justify-center p-2 transition duration-150 ease-in-out rounded-md text-primary/80 hover:text-primary hover:bg-white/50 focus:outline-none focus:bg-white/60 focus:text-primary/90"
                        @click="
                            showingNavigationDropdown =
                                !showingNavigationDropdown
                        "
                    >
                        <svg
                            class="size-6"
                            stroke="currentColor"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <path
                                :class="{
                                    hidden: showingNavigationDropdown,
                                    'inline-flex': !showingNavigationDropdown,
                                }"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                            <path
                                :class="{
                                    hidden: !showingNavigationDropdown,
                                    'inline-flex': showingNavigationDropdown,
                                }"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div
            :class="{
                block: showingNavigationDropdown,
                hidden: !showingNavigationDropdown,
            }"
            class="md:hidden bg-primary"
        >
            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="flex items-center px-4">
                    <img
                        v-if="$page.props.auth.user.avatar"
                        class="object-cover rounded-full size-12 me-3"
                        :src="$page.props.auth.user.avatar"
                        :alt="$page.props.auth.user.name"
                    />
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        width="44"
                        height="44"
                        viewBox="0 0 44 44"
                        class="fill-gray-200 size-12 me-2"
                    >
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M40.3333 22.0003C40.3333 32.1258 32.1255 40.3337 22 40.3337C11.8745 40.3337 3.66663 32.1258 3.66663 22.0003C3.66663 11.8748 11.8745 3.66699 22 3.66699C32.1255 3.66699 40.3333 11.8748 40.3333 22.0003ZM27.5 16.5003C27.5 17.959 26.9205 19.358 25.889 20.3894C24.8576 21.4209 23.4586 22.0003 22 22.0003C20.5413 22.0003 19.1423 21.4209 18.1109 20.3894C17.0794 19.358 16.5 17.959 16.5 16.5003C16.5 15.0416 17.0794 13.6427 18.1109 12.6112C19.1423 11.5798 20.5413 11.0003 22 11.0003C23.4586 11.0003 24.8576 11.5798 25.889 12.6112C26.9205 13.6427 27.5 15.0416 27.5 16.5003ZM22 37.5837C25.1465 37.5887 28.2201 36.6366 30.8128 34.8538C31.9201 34.093 32.3931 32.6447 31.7478 31.4658C30.415 29.022 27.665 27.5003 22 27.5003C16.335 27.5003 13.585 29.022 12.2503 31.4658C11.6068 32.6447 12.0798 34.093 13.1871 34.8538C15.7798 36.6366 18.8535 37.5887 22 37.5837Z"
                        />
                    </svg>

                    <div>
                        <div class="text-base font-medium text-gray-200">
                            {{ $page.props.auth.user.name }}
                        </div>
                        <div class="text-sm font-medium text-gray-300">
                            {{ $page.props.auth.user.email }}
                        </div>
                    </div>
                </div>

                <AdminSidebar :responsive="true">
                    <template #extra>
                        <!-- divider -->
                        <div class="mx-6 my-2 border-t border-secondary/20" />

                        <div class="space-y-0.5 bg-primary">
                            <ResponsiveNavLink
                                :href="route('profile')"
                                :active="route().current('profile.show')"
                                class="[&>*]:text-gray-200"
                            >
                                Profile
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink
                                    as="button"
                                    class="[&>*]:text-gray-200"
                                >
                                    Log Out
                                </ResponsiveNavLink>
                            </form>
                        </div>
                    </template>
                </AdminSidebar>
            </div>
        </div>
    </nav>
</template>

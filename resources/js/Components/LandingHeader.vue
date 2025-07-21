<script setup lang="ts">
import { computed, ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import CartButton from "./CartButton.vue";
import { useCartStore } from "@/stores/cart-store";
import DropdownLink from "./DropdownLink.vue";
import Dropdown from "./Dropdown.vue";
import MyOrderButton from "./MyOrderButton.vue";
import Tooltip from "./Tooltip.vue";

const cartStore = useCartStore();

const showingNavigationDropdown = ref(false);

const menus = [
    {
        name: "Tentang Kami",
        href: route().current("home") ? "#about" : route("home") + "#about",
        active: route().current("home"),
    },
    {
        name: "Katalog",
        href: route("catalog"),
        active: route().current("catalog"),
    },
    {
        name: "Gabung",
        href: "#join",
        active: false,
    },
];

const trailingMenus = [
    {
        name: "Masuk",
        href: route("login"),
        active: route().current("login"),
    },
];

const logout = () => {
    localStorage.removeItem("access_token");
    router.post(route("logout"));
};
</script>

<template>
    <nav
        class="sticky top-0 z-50 py-1 bg-primary sm:px-12 md:px-[100px] w-full"
    >
        <!-- Primary Navigation Menu -->
        <div class="px-6 mx-auto max-w-7xl sm:px-0">
            <div class="flex justify-between h-16">
                <div class="flex justify-between w-full">
                    <!-- Logo -->
                    <div class="flex items-center shrink-0">
                        <Link :href="route('home')">
                            <img
                                src="/storage/logo_white.png"
                                alt="Logo"
                                class="w-auto h-12 sm:h-16"
                            />
                        </Link>
                    </div>

                    <div class="flex items-center gap-4 sm:gap-6">
                        <!-- Navigation Links -->
                        <div
                            class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex sm:items-center"
                        >
                            <!-- Menus -->
                            <NavLink
                                v-for="menu in menus"
                                :key="menu.name"
                                :href="menu.href"
                                :active="menu.active"
                                :class="{
                                    '!text-white': menu.active,
                                }"
                            >
                                {{ menu.name }}
                            </NavLink>
                        </div>

                        <!-- Divider -->
                        <span
                            class="hidden w-px h-6 sm:inline-block bg-white/20"
                        ></span>

                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2 me-2 sm:me-0">
                                <!-- My Cart -->
                                <Tooltip
                                    id="tooltip-cart"
                                    placement="bottom"
                                    class="col-span-1 sm:col-span-2"
                                >
                                    <template #content>
                                        <p>Keranjang Saya</p>
                                    </template>
                                    <CartButton
                                        :length="cartStore.items.length"
                                        @click="$inertia.get(route('my-cart'))"
                                    />
                                </Tooltip>

                                <!-- My Order -->
                                <Tooltip
                                    id="tooltip-my-order"
                                    placement="bottom"
                                    class="col-span-1 sm:col-span-2"
                                >
                                    <template #content>
                                        <p>Pesanan Saya</p>
                                    </template>
                                    <MyOrderButton
                                        v-if="$page.props.auth.user"
                                        @click="$inertia.get(route('my-order'))"
                                    />
                                </Tooltip>
                            </div>

                            <!-- Trailing Menus -->
                            <div class="hidden gap-x-8 sm:flex sm:items-center">
                                <template v-if="!$page.props.auth.user">
                                    <NavLink
                                        v-for="menu in trailingMenus"
                                        :key="menu.name"
                                        :href="menu.href"
                                        :active="menu.active"
                                        :class="{
                                            '!text-white': menu.active,
                                        }"
                                    >
                                        {{ menu.name }}
                                    </NavLink>
                                </template>

                                <!-- Settings Dropdown -->
                                <div
                                    v-if="$page.props.auth.user"
                                    class="relative"
                                >
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <span
                                                class="inline-flex rounded-md"
                                            >
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out bg-transparent border border-transparent rounded-md text-white/90 hover:text-white focus:outline-none hover:bg-white/10 focus:bg-white/10 active:bg-white/20"
                                                >
                                                    <img
                                                        v-if="
                                                            $page.props.auth
                                                                .user.avatar
                                                        "
                                                        class="object-cover rounded-full size-8 me-2"
                                                        :src="
                                                            $page.props.auth
                                                                .user.avatar
                                                        "
                                                        :alt="
                                                            $page.props.auth
                                                                .user.name
                                                        "
                                                    />
                                                    <svg
                                                        v-else
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        width="44"
                                                        height="44"
                                                        viewBox="0 0 44 44"
                                                        class="fill-white/90 size-8 me-1.5"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            clip-rule="evenodd"
                                                            d="M40.3333 22.0003C40.3333 32.1258 32.1255 40.3337 22 40.3337C11.8745 40.3337 3.66663 32.1258 3.66663 22.0003C3.66663 11.8748 11.8745 3.66699 22 3.66699C32.1255 3.66699 40.3333 11.8748 40.3333 22.0003ZM27.5 16.5003C27.5 17.959 26.9205 19.358 25.889 20.3894C24.8576 21.4209 23.4586 22.0003 22 22.0003C20.5413 22.0003 19.1423 21.4209 18.1109 20.3894C17.0794 19.358 16.5 17.959 16.5 16.5003C16.5 15.0416 17.0794 13.6427 18.1109 12.6112C19.1423 11.5798 20.5413 11.0003 22 11.0003C23.4586 11.0003 24.8576 11.5798 25.889 12.6112C26.9205 13.6427 27.5 15.0416 27.5 16.5003ZM22 37.5837C25.1465 37.5887 28.2201 36.6366 30.8128 34.8538C31.9201 34.093 32.3931 32.6447 31.7478 31.4658C30.415 29.022 27.665 27.5003 22 27.5003C16.335 27.5003 13.585 29.022 12.2503 31.4658C11.6068 32.6447 12.0798 34.093 13.1871 34.8538C15.7798 36.6366 18.8535 37.5887 22 37.5837Z"
                                                        />
                                                    </svg>

                                                    {{
                                                        $page.props.auth.user
                                                            .name
                                                    }}

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
                                            <DropdownLink
                                                :href="route('store.create')"
                                            >
                                                Buat Toko
                                            </DropdownLink>

                                            <div
                                                class="border-t border-gray-200"
                                            />

                                            <DropdownLink
                                                :href="route('profile')"
                                            >
                                                Profile
                                            </DropdownLink>

                                            <div
                                                class="border-t border-gray-200"
                                            />

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
                        </div>
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="flex items-center -me-2 sm:hidden">
                    <button
                        class="inline-flex items-center justify-center p-2 transition duration-150 ease-in-out rounded-md text-white/80 hover:text-white hover:bg-white/10 focus:outline-none focus:bg-white/10 focus:hover:bg-white/20 focus:text-white"
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
            class="pb-2 sm:hidden"
        >
            <ul>
                <li v-for="menu in menus" :key="menu.name">
                    <NavLink
                        :href="menu.href"
                        :active="menu.active"
                        active-class="!bg-secondary !border-[#CE4DB1] text-primary"
                        class="px-4 py-2.5 w-full bg-transparent hover:bg-secondary/10 hover:border-l-4 hover:border-[#CE4DB1] border-l-4 transition-all duration-300 ease-in-out border-primary"
                        :class="{
                            '!text-primary': menu.active,
                        }"
                    >
                        {{ menu.name }}
                    </NavLink>
                </li>
            </ul>

            <div class="h-px mx-5 my-2 bg-white/10"></div>

            <ul>
                <!-- Trailing Menus -->
                <template v-if="!$page.props.auth.user">
                    <li v-for="menu in trailingMenus" :key="menu.name">
                        <NavLink
                            :href="menu.href"
                            :active="menu.active"
                            active-class="!bg-secondary !border-[#CE4DB1] text-primary"
                            class="px-4 py-2.5 w-full bg-transparent hover:bg-secondary/10 hover:border-l-4 hover:border-[#CE4DB1] border-l-4 transition-all duration-300 ease-in-out border-primary"
                            :class="{
                                '!text-primary': menu.active,
                            }"
                        >
                            {{ menu.name }}
                        </NavLink>
                    </li>
                </template>

                <!-- Settings Dropdown -->
                <li v-if="$page.props.auth.user">
                    <div class="relative">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center w-full px-4 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out bg-transparent border border-transparent text-white/90 hover:text-white focus:outline-none hover:bg-white/10 focus:bg-white/10 active:bg-white/20"
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
                                        class="fill-white/90 size-8 me-1.5"
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
                </li>
            </ul>
        </div>
    </nav>
</template>

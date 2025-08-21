<script setup lang="ts">
import { computed, ref, onMounted } from "vue";
import { Link, router } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import CartButton from "./CartButton.vue";
import { useCartStore } from "@/stores/cart-store";
import MyOrderButton from "./MyOrderButton.vue";
import Tooltip from "./Tooltip.vue";
import StoreOptionsDialog from "./StoreOptionsDialog.vue";
import { useMyStoreStore } from "@/stores/my-store-store";
import UserDropdown from "./UserDropdown.vue";
import HamburgerButton from "./HamburgerButton.vue";

const cartStore = useCartStore();
const myStoreStore = useMyStoreStore();

const showingNavigationDropdown = ref(false);

const menus = [
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

const showStoreOptionsDialog = ref(false);

const scrolled = ref(false);
const scrollThreshold = 50;

const handleScroll = () => {
    scrolled.value = window.scrollY > scrollThreshold;
};

onMounted(() => {
    window.addEventListener("scroll", handleScroll);
});
</script>

<template>
    <nav
        class="sticky top-0 z-50 py-1 bg-white sm:px-12 lg:px-[100px] w-full transition-all duration-300 ease-in-out border-b border-gray-100"
        :class="{
            'border-b border-primary-box !bg-primary-box': scrolled,
        }"
    >
        <!-- Primary Navigation Menu -->
        <div class="px-6 mx-auto max-w-7xl sm:px-0">
            <div class="flex justify-between h-16">
                <div class="flex justify-between w-full">
                    <!-- Logo -->
                    <div class="flex items-center shrink-0">
                        <Link :href="route('home')">
                            <img
                                :src="
                                    scrolled
                                        ? `/storage/${$page.props.setting.logo_white}`
                                        : `/storage/${$page.props.setting.logo_black}`
                                "
                                alt="Logo"
                                class="w-auto h-9 sm:h-10"
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
                                class="text-sm font-normal"
                                :class="{
                                    '!text-primary': menu.active,
                                    '!text-gray-500 hover:!text-gray-600':
                                        !scrolled && !menu.active,
                                }"
                            >
                                {{ menu.name }}
                            </NavLink>
                        </div>

                        <!-- Divider -->
                        <span
                            class="hidden w-px h-6 bg-gray-300 sm:inline-block"
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
                                        :invert="scrolled"
                                        :active="route().current('my-cart')"
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
                                        :invert="scrolled"
                                        :active="route().current('my-order')"
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
                                        class="text-sm font-normal"
                                        :class="{
                                            '!text-primary': menu.active,
                                            '!text-gray-500 hover:!text-gray-600':
                                                !scrolled && !menu.active,
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
                                    <UserDropdown
                                        :invert="scrolled"
                                        @showStoreOptionsDialog="
                                            showStoreOptionsDialog = true
                                        "
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="flex items-center -me-2 sm:hidden">
                    <HamburgerButton
                        :invert="scrolled"
                        :active="showingNavigationDropdown"
                        @toggle="
                            showingNavigationDropdown =
                                !showingNavigationDropdown
                        "
                    />
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
                        active-class="!bg-primary/10 !border-primary text-primary"
                        class="px-4 py-2.5 w-full bg-transparent !text-gray-500 font-normal hover:bg-primary/5 hover:border-l-4 hover:border-primary/50 border-l-4 transition-all duration-300 ease-in-out"
                        :class="{
                            '!text-primary-dark hover:!text-primary-dark':
                                menu.active && !scrolled,
                            '!text-primary hover:!text-primary':
                                menu.active && scrolled,
                            '!text-white/80 hover:!text-white/80 hover:!bg-white/10 focus:!bg-white/10 border-transparent':
                                scrolled && !menu.active,
                        }"
                    >
                        {{ menu.name }}
                    </NavLink>
                </li>
            </ul>

            <div
                class="h-px mx-5 my-2 transition-all duration-300 ease-in-out bg-gray-300"
                :class="{
                    'bg-white/20': scrolled,
                }"
            ></div>

            <ul>
                <!-- Trailing Menus -->
                <template v-if="!$page.props.auth.user">
                    <li v-for="menu in trailingMenus" :key="menu.name">
                        <NavLink
                            :href="menu.href"
                            :active="menu.active"
                            active-class="!bg-primary/10 !border-primary text-primary"
                            class="px-4 py-2.5 w-full bg-transparent !text-gray-500 font-normal hover:bg-primary/5 hover:border-l-4 hover:border-primary/50 border-l-4 transition-all duration-300 ease-in-out"
                            :class="{
                                '!text-primary-dark hover:!text-primary-dark':
                                    menu.active && !scrolled,
                                '!text-primary hover:!text-primary':
                                    menu.active && scrolled,
                                '!text-white/80 hover:!text-white/80 hover:!bg-white/10 focus:!bg-white/10 border-transparent':
                                    scrolled && !menu.active,
                            }"
                        >
                            {{ menu.name }}
                        </NavLink>
                    </li>
                </template>

                <!-- Settings Dropdown -->
                <li v-if="$page.props.auth.user">
                    <div class="relative">
                        <UserDropdown
                            :invert="scrolled"
                            @showStoreOptionsDialog="
                                showStoreOptionsDialog = true
                            "
                        />
                    </div>
                </li>
            </ul>
        </div>

        <StoreOptionsDialog
            v-if="$page.props.auth.user?.stores"
            :title="'Pilih Toko'"
            :stores="$page.props.auth.user.stores"
            :show="showStoreOptionsDialog"
            @close="showStoreOptionsDialog = false"
            @select="
                myStoreStore.setSelectedStoreId($event.id);
                $inertia.visit(
                    route('my-store.select-store', { storeId: $event.id })
                );

                showStoreOptionsDialog = false;
            "
        />
    </nav>
</template>

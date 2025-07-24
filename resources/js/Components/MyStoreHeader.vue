<script setup>
import { defineProps, ref, onMounted } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import UserDropdown from "./UserDropdown.vue";
import MyStoreSidebar from "./MyStoreSidebar.vue";
import NavLink from "./NavLink.vue";
import StoreItem from "./StoreItem.vue";
import StoreOptionsDialog from "./StoreOptionsDialog.vue";
import { useMyStoreStore } from "@/stores/my-store-store";
import HamburgerButton from "./HamburgerButton.vue";

const props = defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const myStoreStore = useMyStoreStore();
const showStoreOptionsDialog = ref(false);
</script>

<template>
    <nav
        class="bg-white z-50 py-1 h-auto sm:h-[72px] fixed top-0 md:w-[calc(100vw-256px)] w-full border-b border-gray-100"
        :class="{
            '!h-auto': showingNavigationDropdown,
        }"
    >
        <!-- Primary Navigation Menu -->
        <div
            class="px-4 mx-auto transition-all duration-300 ease-in-out sm:px-6 lg:px-8"
        >
            <div class="flex items-center justify-between h-16">
                <slot name="leading" />

                <div class="hidden md:flex sm:items-center sm:ms-6">
                    <!-- Settings Dropdown -->
                    <div class="relative ms-3">
                        <UserDropdown
                            @showStoreOptionsDialog="
                                showStoreOptionsDialog = true
                            "
                        />
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="flex items-center -me-2 md:hidden">
                    <HamburgerButton
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
            class="bg-white md:hidden"
        >
            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200">
                <!-- Store Card -->
                <div v-if="$page.props.selected_store" class="w-full px-4">
                    <StoreItem
                        :name="$page.props.selected_store.name"
                        :description="$page.props.selected_store.description"
                        :icon="$page.props.selected_store.icon"
                        @click="showStoreOptionsDialog = true"
                    >
                        <template #trailing>
                            <button
                                type="button"
                                class="absolute p-2 right-1.5"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    class="size-4 fill-gray-400"
                                >
                                    <path
                                        d="M18.6054 7.3997C18.4811 7.273 18.3335 7.17248 18.1709 7.10389C18.0084 7.0353 17.8342 7 17.6583 7C17.4823 7 17.3081 7.0353 17.1456 7.10389C16.9831 7.17248 16.8355 7.273 16.7112 7.3997L11.4988 12.7028L6.28648 7.3997C6.03529 7.14415 5.69462 7.00058 5.33939 7.00058C4.98416 7.00058 4.64348 7.14415 4.3923 7.3997C4.14111 7.65526 4 8.00186 4 8.36327C4 8.72468 4.14111 9.07129 4.3923 9.32684L10.5585 15.6003C10.6827 15.727 10.8304 15.8275 10.9929 15.8961C11.1554 15.9647 11.3296 16 11.5055 16C11.6815 16 11.8557 15.9647 12.0182 15.8961C12.1807 15.8275 12.3284 15.727 12.4526 15.6003L18.6188 9.32684C19.1293 8.80747 19.1293 7.93274 18.6054 7.3997Z"
                                    />
                                </svg>
                            </button>
                        </template>
                    </StoreItem>
                </div>

                <MyStoreSidebar :responsive="true">
                    <template #extra>
                        <!-- divider -->
                        <div class="mx-6 my-2 border-t border-gray-200" />

                        <div class="space-y-0.5 relative">
                            <UserDropdown
                                :responsive="true"
                                @showStoreOptionsDialog="
                                    showStoreOptionsDialog = true
                                "
                            />
                        </div>
                    </template>
                </MyStoreSidebar>
            </div>
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

<script setup>
import { ref, Transition } from "vue";
import UserDropdown from "./UserDropdown.vue";
import MyStoreSidebar from "./MyStoreSidebar.vue";
import StoreItem from "./StoreItem.vue";
import StoreOptionsDialog from "./StoreOptionsDialog.vue";
import { useMyStoreStore } from "@/stores/my-store-store";
import HamburgerButton from "./HamburgerButton.vue";
import { getImageUrl } from "@/plugins/helpers";
import { useScreenSize } from "@/plugins/screen-size";

const props = defineProps({
    title: String,
});

const screenSize = useScreenSize();

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
            <div
                class="flex items-center justify-between h-16"
                :class="{
                    'justify-end': !$slots.leading,
                }"
            >
                <slot name="leading" />

                <div class="hidden md:flex sm:items-center sm:ms-6">
                    <!-- Selected Store -->
                    <button
                        v-if="$page.props.selected_store"
                        type="button"
                        class="flex items-center justify-start w-full gap-2 px-3 py-2 transition-all duration-200 ease-in-out bg-white border border-transparent border-gray-200 rounded-md cursor-pointer hover:bg-gray-100"
                        @click="showStoreOptionsDialog = true"
                    >
                        <img
                            v-if="$page.props.selected_store.logo && false"
                            :src="getImageUrl($page.props.selected_store.logo)"
                            alt="Platform Icon"
                            class="text-blue-500 !fill-blue-500 object-contain size-8"
                        />
                        <div v-else class="p-0.5">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="25"
                                height="24"
                                viewBox="0 0 25 24"
                                class="fill-gray-400 size-7"
                            >
                                <path
                                    d="M22 7.82001C22.006 7.75682 22.006 7.6932 22 7.63001L20 2.63001C19.9219 2.43237 19.7828 2.26475 19.603 2.15147C19.4232 2.03819 19.212 1.98514 19 2.00001H5C4.79972 1.99982 4.604 2.05977 4.43818 2.17209C4.27237 2.28442 4.1441 2.44395 4.07 2.63001L2.07 7.63001C2.06397 7.6932 2.06397 7.75682 2.07 7.82001C2.0371 7.87584 2.01346 7.93663 2 8.00001C2.01113 8.69125 2.20123 9.36781 2.55174 9.96369C2.90226 10.5596 3.40124 11.0544 4 11.4V21C4 21.2652 4.10536 21.5196 4.29289 21.7071C4.48043 21.8947 4.73478 22 5 22H19C19.2652 22 19.5196 21.8947 19.7071 21.7071C19.8946 21.5196 20 21.2652 20 21V11.44C20.6046 11.091 21.1072 10.5898 21.4581 9.98635C21.809 9.38287 21.9958 8.69807 22 8.00001C22.0091 7.94035 22.0091 7.87967 22 7.82001ZM13 20H11V16H13V20ZM18 20H15V15C15 14.7348 14.8946 14.4804 14.7071 14.2929C14.5196 14.1054 14.2652 14 14 14H10C9.73478 14 9.48043 14.1054 9.29289 14.2929C9.10536 14.4804 9 14.7348 9 15V20H6V12C6.56947 11.9968 7.13169 11.872 7.64905 11.634C8.16642 11.3961 8.627 11.0503 9 10.62C9.37537 11.0456 9.83701 11.3865 10.3542 11.62C10.8715 11.8535 11.4325 11.9743 12 11.9743C12.5675 11.9743 13.1285 11.8535 13.6458 11.62C14.163 11.3865 14.6246 11.0456 15 10.62C15.373 11.0503 15.8336 11.3961 16.3509 11.634C16.8683 11.872 17.4305 11.9968 18 12V20ZM18 10C17.4696 10 16.9609 9.7893 16.5858 9.41423C16.2107 9.03915 16 8.53044 16 8.00001C16 7.7348 15.8946 7.48044 15.7071 7.29291C15.5196 7.10537 15.2652 7.00001 15 7.00001C14.7348 7.00001 14.4804 7.10537 14.2929 7.29291C14.1054 7.48044 14 7.7348 14 8.00001C14 8.53044 13.7893 9.03915 13.4142 9.41423C13.0391 9.7893 12.5304 10 12 10C11.4696 10 10.9609 9.7893 10.5858 9.41423C10.2107 9.03915 10 8.53044 10 8.00001C10 7.7348 9.89464 7.48044 9.70711 7.29291C9.51957 7.10537 9.26522 7.00001 9 7.00001C8.73478 7.00001 8.48043 7.10537 8.29289 7.29291C8.10536 7.48044 8 7.7348 8 8.00001C8.00985 8.26266 7.96787 8.52467 7.87646 8.77109C7.78505 9.01751 7.646 9.24351 7.46725 9.43619C7.28849 9.62887 7.07354 9.78446 6.83466 9.89407C6.59578 10.0037 6.33764 10.0652 6.075 10.075C5.54457 10.0949 5.02796 9.90327 4.63882 9.54226C4.44614 9.36351 4.29055 9.14855 4.18094 8.90967C4.07133 8.67079 4.00985 8.41266 4 8.15001L5.68 4.00001H18.32L20 8.15001C19.9621 8.65403 19.7348 9.125 19.3637 9.46822C18.9927 9.81143 18.5054 10.0014 18 10Z"
                                />
                            </svg>
                        </div>
                        <p
                            class="text-sm font-medium text-start line-clamp-1 overflow-ellipsis text-gray-500/90"
                        >
                            {{ $page.props.selected_store.name }}
                        </p>
                        <svg
                            class="size-4 text-gray-500/90"
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
        <Transition name="accordion">
            <div
                v-if="!screenSize.is('md') && showingNavigationDropdown"
                class="bg-white"
            >
                <!-- Responsive Settings Options -->
                <div class="pt-4 pb-1 border-t border-gray-200">
                    <!-- Store Card -->
                    <div v-if="$page.props.selected_store" class="w-full px-4">
                        <StoreItem
                            :name="$page.props.selected_store.name"
                            :description="
                                $page.props.selected_store
                                    .rajaongkir_origin_label
                            "
                            :icon="getImageUrl($page.props.selected_store.logo)"
                            @click="showStoreOptionsDialog = true"
                        >
                            <template #trailing>
                                <button type="button" class="p-2">
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
                            <!-- <div class="mx-6 my-2 border-t border-gray-200" /> -->

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
        </Transition>

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

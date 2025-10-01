<script setup>
import { ref, Transition } from "vue";
import HamburgerButton from "./HamburgerButton.vue";
import { useScreenSize } from "@/plugins/screen-size";

const screenSize = useScreenSize();

const showingNavigationDropdown = ref(false);
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
            class="px-4 mx-auto transition-all duration-300 ease-in-out sm:px-6 lg:px-7"
        >
            <div
                class="flex items-center justify-between h-16"
                :class="{
                    'justify-end': !$slots.leading,
                }"
            >
                <slot name="leading" />

                <div class="hidden md:flex sm:items-center sm:ms-6">
                    <slot name="trailing" />
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
                <div v-if="$slots.responsiveMenu">
                    <slot name="responsiveMenu" />
                </div>
            </div>
        </Transition>
    </nav>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
import MyStoreHeader from "@/Components/MyStoreHeader.vue";
import MyStoreSidebar from "@/Components/MyStoreSidebar.vue";

defineProps({
    title: String,
    showTitle: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <!-- Page Content -->
        <div class="flex h-screen bg-secondary-box">
            <!-- Sidebar -->
            <MyStoreSidebar />

            <div
                class="flex-1 w-full transition-all duration-300 ease-in-out md:ml-64 h-[calc(100vh-72px)] sm:min-h-[calc(100vh-72px)] relative mt-[72px] overflow-x-hidden"
            >
                <!-- Header -->
                <MyStoreHeader>
                    <template v-if="showTitle" #leading>
                        <div
                            class="flex items-center justify-between gap-4 rounded-lg"
                        >
                            <div
                                class="flex items-center gap-2 sm:gap-4 max-w-7xl"
                            >
                                <h1 class="text-lg font-semibold sm:text-xl">
                                    {{ title }}
                                </h1>
                            </div>
                            <slot name="trailing"></slot>
                        </div>
                    </template>
                </MyStoreHeader>

                <!-- Main Content -->
                <main class="sm:p-6">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

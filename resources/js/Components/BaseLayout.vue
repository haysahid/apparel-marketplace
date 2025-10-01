<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";

defineProps({
    title: String,
});
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="flex h-screen bg-secondary-box">
            <slot name="sidebar" />

            <div
                id="main-area"
                class="flex-1 w-full transition-all duration-300 ease-in-out md:ml-64 h-[calc(100vh-72px)] sm:min-h-[calc(100vh-72px)] relative mt-[72px] overflow-x-hidden"
            >
                <slot name="header" />

                <main class="sm:p-6">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

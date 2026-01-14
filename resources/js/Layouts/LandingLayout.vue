<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
import LandingFooter from "@/Components/LandingFooter.vue";
import LandingHeader from "@/Components/LandingHeader.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import { useDialogStore } from "@/stores/dialog-store";

defineProps({
    title: String,
});

const dialogStore = useDialogStore();
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="relative w-full min-h-screen bg-white">
            <!-- Header -->
            <LandingHeader />

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>

            <!-- Footer -->
            <LandingFooter />
        </div>

        <SuccessDialog
            :show="dialogStore.showSuccessDialog"
            :title="dialogStore.successMessage"
            @close="dialogStore.showSuccessDialog = false"
        />

        <ErrorDialog
            :show="dialogStore.showErrorDialog"
            @close="dialogStore.showErrorDialog = false"
        >
            <template #content>
                <div>
                    <div
                        class="mb-1 text-lg font-semibold text-center text-gray-900"
                    >
                        Terjadi Kesalahan
                    </div>
                    <p class="text-sm text-center text-gray-700">
                        {{ dialogStore.errorMessage }}
                    </p>
                </div>
            </template>
        </ErrorDialog>
    </div>
</template>

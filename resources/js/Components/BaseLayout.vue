<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
import SuccessDialog from "./SuccessDialog.vue";
import ErrorDialog from "./ErrorDialog.vue";
import { useDialogStore } from "@/stores/dialog-store";
import ImageViewer from "./ImageViewer.vue";
import { useImageViewerStore } from "@/stores/image-viewer-store";
import RawModal from "./RawModal.vue";

defineProps({
    title: String,
});

const dialogStore = useDialogStore();
const imageViewerStore = useImageViewerStore();
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

                <main class="mx-auto sm:p-6 max-w-7xl">
                    <slot />
                </main>
            </div>
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

        <RawModal
            :show="imageViewerStore.selectedImage !== null"
            closeable
            @close="imageViewerStore.selectedImage = null"
        >
            <div class="max-w-4xl mx-auto overflow-hidden">
                <img
                    :src="
                        $getImageUrl(
                            imageViewerStore.selectedImage?.original_url,
                        )
                    "
                    :alt="imageViewerStore.selectedImage?.name"
                    class="h-auto object-contain max-h-[80vh] w-fit"
                />
            </div>
        </RawModal>
    </div>
</template>

<script setup>
import DialogModal from "@/Components/DialogModal.vue";

const props = defineProps({
    title: {
        type: String,
        default: "Berhasil!",
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close", "delete"]);

const close = () => {
    emit("close");
};

import { ref, watch, onBeforeUnmount } from "vue";

let timer = null;
const progress = ref(100); // percent
const duration = 2000; // ms

watch(
    () => props.show,
    (show) => {
        if (show) {
            progress.value = 100;
            const step = 10;
            const interval = duration / (100 / step);
            timer = setInterval(() => {
                progress.value -= step;
                if (progress.value <= 0) {
                    progress.value = 0;
                    clearInterval(timer);
                    timer = null;
                    close();
                }
            }, interval);
        } else {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
            progress.value = 100;
        }
    }
);

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
});
</script>
<template>
    <DialogModal
        :show="props.show"
        :showCloseButton="true"
        @close="close"
        maxWidth="sm"
    >
        <template #icon>
            <div class="p-2">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="64"
                    height="64"
                    viewBox="0 0 64 64"
                    fill="none"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M31.7543 63.4159C35.9122 63.4159 40.0293 62.5969 43.8707 61.0058C47.712 59.4146 51.2024 57.0824 54.1424 54.1424C57.0824 51.2024 59.4146 47.712 61.0058 43.8707C62.5969 40.0293 63.4159 35.9122 63.4159 31.7543C63.4159 27.5965 62.5969 23.4793 61.0058 19.638C59.4146 15.7966 57.0824 12.3063 54.1424 9.36622C51.2024 6.42618 47.712 4.09401 43.8707 2.50286C40.0293 0.911723 35.9122 0.0927734 31.7543 0.0927734C23.3572 0.0927736 15.3039 3.42853 9.36622 9.36622C3.42853 15.3039 0.0927734 23.3572 0.0927734 31.7543C0.0927734 40.1515 3.42853 48.2047 9.36622 54.1424C15.3039 60.0801 23.3572 63.4159 31.7543 63.4159ZM30.9381 44.5596L48.5279 23.452L43.1243 18.949L27.9971 37.0981L20.1697 29.2671L15.1953 34.2415L25.7492 44.7953L28.4721 47.5182L30.9381 44.5596Z"
                        fill="#27D04D"
                    />
                </svg>
            </div>
        </template>
        <template #title>
            <p class="text-lg font-medium text-gray-900">
                {{ props.title }}
            </p>
        </template>
    </DialogModal>
</template>

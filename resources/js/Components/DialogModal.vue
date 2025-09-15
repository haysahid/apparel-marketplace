<script setup>
import { useSlots } from "vue";
import Modal from "./Modal.vue";

const emit = defineEmits(["close"]);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: "2xl",
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    showCloseButton: {
        type: Boolean,
        default: false,
    },
    dialogClass: {
        type: String,
        default: "",
    },
    containerClass: {
        type: String,
        default: "",
    },
});

const close = () => {
    emit("close");
};

const slots = useSlots();

const hasIconSlot = !!slots.icon;
const hasTitleSlot = !!slots.title;
const hasContentSlot = !!slots.content;
const hasFooterSlot = !!slots.footer;
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <div class="relative max-h-[80vh] overflow-y-auto" :class="dialogClass">
            <button
                v-if="showCloseButton"
                type="button"
                class="absolute p-2 text-gray-400 bg-white rounded-full top-1 right-1 hover:bg-gray-100"
                @click="close"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-6 h-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
            <div class="flex flex-col items-center p-6" :class="containerClass">
                <div
                    v-if="hasIconSlot"
                    class="mb-2 text-lg font-medium text-center text-gray-900"
                >
                    <slot name="icon" />
                </div>

                <div
                    v-if="hasTitleSlot"
                    class="sticky top-0 z-10 mb-2 text-lg font-medium text-center text-gray-900"
                >
                    <slot name="title" />
                </div>

                <div
                    v-if="hasContentSlot"
                    class="flex flex-col items-center w-full text-center text-gray-600"
                >
                    <slot name="content" />
                </div>

                <div
                    v-if="hasFooterSlot"
                    class="flex flex-row justify-center mt-2 text-end"
                >
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </Modal>
</template>

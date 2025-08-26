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

const emit = defineEmits(["close"]);

const close = () => {
    emit("close");
};
</script>
<template>
    <DialogModal :show="props.show" @close="close" maxWidth="xs">
        <template #icon>
            <div class="p-2">
                <div class="circular-loading"></div>
            </div>
        </template>
        <template #title>{{ props.title }}</template>
    </DialogModal>
</template>

<script setup>
import DialogModal from "@/Components/DialogModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    title: {
        type: String,
        default: "Anda Yakin Menghapus Data Ini?",
    },
    description: {
        type: String,
        default: null,
    },
    positiveButtonText: {
        type: String,
        default: "Hapus Data",
    },
    negativeButtonText: {
        type: String,
        default: "Batalkan",
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
</script>
<template>
    <DialogModal :show="props.show" @close="close" maxWidth="sm">
        <template #icon v-if="$slots.icon">
            <slot name="icon" />
        </template>
        <template #title>
            <h3
                v-html="props.title"
                class="text-lg font-medium leading-6 text-gray-900 text-wrap"
            ></h3>
        </template>
        <template v-if="props.description || $slots.content" #content>
            <p class="mt-0.5 mb-1.5 text-center text-wrap">
                {{ props.description }}
            </p>
            <slot name="content" />
        </template>
        <slot />
        <template #footer>
            <div class="flex gap-4 text-base">
                <SecondaryButton
                    type="button"
                    class=""
                    @click.stop="emit('close')"
                >
                    Batalkan
                </SecondaryButton>
                <PrimaryButton
                    type="button"
                    class="bg-red-500 hover:bg-red-500/80 active:bg-red-500/90 focus:bg-red-500 focus:ring-red-500"
                    @click.stop="emit('delete')"
                >
                    {{ props.positiveButtonText }}
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
</template>

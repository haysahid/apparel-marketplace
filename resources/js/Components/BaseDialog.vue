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
        default: "Ya",
    },
    negativeButtonText: {
        type: String,
        default: "Batalkan",
    },
    showNegativeButton: {
        type: Boolean,
        default: true,
    },
    show: {
        type: Boolean,
        default: false,
    },
    reverseButton: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close", "positiveClicked", "negativeClicked"]);

const close = () => {
    emit("close");
};
</script>
<template>
    <DialogModal :show="props.show" @close="close" maxWidth="sm">
        <template #title>
            <h3
                v-html="props.title"
                class="text-lg font-medium leading-6 text-gray-900 text-wrap"
            ></h3>
        </template>
        <template v-if="props.description" #content>
            <p class="mt-0.5 mb-1.5 text-center text-wrap">
                {{ props.description }}
            </p>
        </template>
        <slot />
        <template #footer>
            <div
                class="flex gap-4 text-base"
                :class="{
                    'flex-row-reverse': props.reverseButton,
                }"
            >
                <SecondaryButton
                    v-if="props.showNegativeButton"
                    type="button"
                    class=""
                    @click="
                        emit('negativeClicked');
                        emit('close');
                    "
                >
                    {{ props.negativeButtonText }}
                </SecondaryButton>
                <PrimaryButton type="button" @click="emit('positiveClicked')">
                    {{ props.positiveButtonText }}
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
</template>

<script setup lang="ts">
import { ref } from "vue";
import DialogModal from "@/Components/DialogModal.vue";
import Chip from "@/Components/Chip.vue";

interface StatusOption {
    value: string;
    label: string;
    disabled: boolean;
}

const props = defineProps({
    currentStatus: {
        type: String,
        required: true,
    },
    options: {
        type: Array as () => StatusOption[],
        required: true,
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["change", "close"]);

const selectedStatus = ref<string | null>(props.currentStatus);

const hexCode = (status: string) => {
    switch (status) {
        case "pending":
            return "#6b7280"; // Gray
        case "paid":
            return "#f97316"; // Orange
        case "processing":
            return "#6366f1"; // Indigo
        case "completed":
            return "#22c55e"; // Green
        case "cancelled":
            return "#ef4444"; // Red
        default:
            return "#6b7280"; // Gray
    }
};

const bgHexCode = (status: string) => {
    switch (status) {
        case "pending":
            return "#f3f4f6"; // Light Gray
        case "paid":
            return "#ffedd5"; // Light Orange
        case "processing":
            return "#e0e7ff"; // Light Indigo
        case "completed":
            return "#dcfce7"; // Light Green
        case "cancelled":
            return "#fee2e2"; // Light Red
        default:
            return "#f3f4f6"; // Light Gray
    }
};
</script>

<template>
    <DialogModal :show="props.show" @close="emit('close')" maxWidth="sm">
        <template #title>
            <h3 class="text-lg font-medium leading-6 text-gray-900 text-wrap">
                Ubah Status Transaksi
            </h3>
        </template>
        <template #content>
            <div class="flex flex-wrap justify-center gap-2 mt-2">
                <Chip
                    v-for="option in props.options"
                    :key="option.value"
                    :label="option.label"
                    :selected="selectedStatus === option.value"
                    :disabled="option.disabled"
                    :hexCode="hexCode(option.value)"
                    :bgHexCode="bgHexCode(option.value)"
                    class="!px-4"
                    @click="
                        selectedStatus = option.value;
                        emit('change', option.value);
                    "
                />
            </div>
        </template>
    </DialogModal>
</template>

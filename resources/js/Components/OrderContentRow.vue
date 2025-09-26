<script setup>
import ThreeDotsLoading from "./ThreeDotsLoading.vue";

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: String,
        default: null,
    },
    valueClass: {
        type: String,
        default: "",
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="flex items-center justify-between gap-2">
        <p class="text-sm text-gray-700">{{ props.label }}</p>

        <ThreeDotsLoading v-if="props.isLoading" />
        <slot v-else-if="$slots.value" name="value" />
        <p
            v-else
            class="text-sm font-semibold text-right text-gray-700"
            :class="[props.valueClass]"
        >
            {{ props.value }}
        </p>
    </div>
</template>

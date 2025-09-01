<script setup>
const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        default: null,
    },
});
</script>

<template>
    <div class="flex items-center justify-between gap-2">
        <p class="text-sm text-gray-700">{{ props.name }}</p>
        <p v-if="!$slots.value" class="text-sm font-semibold text-gray-700">
            {{ props.value || "-" }}
        </p>
        <slot v-else name="value"></slot>
    </div>
</template>

<script setup>
defineProps({
    value: String,
    required: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <label class="block text-sm font-medium text-left text-gray-800">
        <span v-if="value">{{ value }}</span>
        <span v-if="required" class="text-red-500">*</span>
        <div v-else><slot /></div>
    </label>
</template>

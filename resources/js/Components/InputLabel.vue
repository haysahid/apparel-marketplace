<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label class="block text-sm font-medium text-left text-gray-800">
        <span v-if="value">{{ value }}</span>
        <div v-else><slot /></div>
    </label>
</template>

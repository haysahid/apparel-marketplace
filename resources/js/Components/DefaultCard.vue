<script setup>
const props = defineProps({
    isMain: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div
        class="p-4 bg-white rounded-lg sm:p-6"
        :class="{ 'max-sm:rounded-none': isMain }"
    >
        <slot />
    </div>
</template>

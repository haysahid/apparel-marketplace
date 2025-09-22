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
        class="p-4 transition-all duration-300 ease-in-out bg-white border border-gray-100 rounded-xl sm:p-6"
        :class="{ 'max-sm:rounded-none': isMain }"
    >
        <slot />
    </div>
</template>

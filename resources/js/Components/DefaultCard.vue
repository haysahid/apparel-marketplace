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
        class="transition-all duration-300 ease-in-out bg-white border border-gray-100 rounded-xl"
        :class="{
            'max-sm:rounded-none border-none p-4 sm:p-6': isMain,
            'p-3 sm:p-4 md:p-6': !isMain,
        }"
    >
        <slot />
    </div>
</template>

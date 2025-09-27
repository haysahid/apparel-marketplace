<script setup>
defineProps({
    title: String,
    value: [String, Number],
});
</script>

<template>
    <div
        class="flex flex-col w-full gap-1 px-4 py-3.5 transition-all duration-300 ease-in-out bg-white border border-gray-100 sm:px-6 sm:py-5 sm:gap-2 rounded-xl"
    >
        <div class="flex items-center gap-1 sm:gap-2">
            <slot name="icon"></slot>
            <h3 class="text-xs font-medium text-gray-500 sm:text-sm">
                {{ title }}
            </h3>
        </div>
        <p class="text-lg font-bold text-gray-900 sm:text-xl">{{ value }}</p>
    </div>
</template>

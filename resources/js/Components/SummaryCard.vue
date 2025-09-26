<script setup>
defineProps({
    title: String,
    value: [String, Number],
});
</script>

<template>
    <div
        class="flex flex-col w-full gap-1 px-6 py-5 transition-all duration-300 ease-in-out bg-white border border-gray-100 sm:gap-2 rounded-xl"
    >
        <div class="flex items-center gap-1 sm:gap-2">
            <slot name="icon"></slot>
            <h3 class="text-sm font-medium text-gray-500">
                {{ title }}
            </h3>
        </div>
        <p class="text-xl font-bold text-gray-900">{{ value }}</p>
    </div>
</template>

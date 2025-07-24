<script setup>
defineProps({
    title: String,
    value: [String, Number],
});
</script>

<template>
    <div
        class="bg-white p-6 flex flex-col gap-3.5 rounded-xl border border-transparent hover:border-primary-light transition-all duration-300 ease-in-out"
    >
        <div class="flex items-center gap-3.5">
            <slot name="icon"></slot>
            <h3 class="text-lg font-bold text-gray-700 sm:text-xl">
                {{ title }}
            </h3>
        </div>
        <p class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ value }}</p>
    </div>
</template>

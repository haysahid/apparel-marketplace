<script setup>
import DefaultCard from "./DefaultCard.vue";

defineProps({
    title: String,
    value: [String, Number],
});
</script>

<template>
    <DefaultCard class="w-full">
        <div class="flex flex-col gap-1 md:gap-2">
            <div class="flex items-center gap-1 sm:gap-2">
                <slot name="icon"></slot>
                <h3 class="text-xs font-medium text-gray-500 sm:text-sm">
                    {{ title }}
                </h3>
            </div>
            <p class="text-base font-bold text-gray-900 sm:text-lg md:text-xl">
                {{ value }}
            </p>
        </div>
    </DefaultCard>
</template>

<script setup>
import DefaultCard from "./DefaultCard.vue";

defineProps({
    title: String,
    value: [String, Number],
    titleColor: {
        type: String,
        default: "text-gray-500",
    },
    valueColor: {
        type: String,
        default: "text-gray-900",
    },
});
</script>

<template>
    <DefaultCard class="w-full">
        <div class="flex flex-col gap-1 md:gap-2">
            <div class="flex items-center gap-1 sm:gap-2">
                <slot name="icon"></slot>
                <h3
                    class="text-xs font-medium sm:text-sm"
                    :class="[titleColor]"
                >
                    {{ title }}
                </h3>
            </div>
            <p
                class="text-base font-bold sm:text-lg md:text-xl"
                :class="[valueColor]"
            >
                {{ value }}
            </p>
        </div>
    </DefaultCard>
</template>

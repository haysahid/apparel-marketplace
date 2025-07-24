<script setup>
import { initFlowbite } from "flowbite";
import { onMounted } from "vue";

const props = defineProps({
    id: {
        type: String,
        default: "tooltip-default",
    },
    placement: {
        type: String,
        default: "top",
    },
});

onMounted(() => {
    initFlowbite();
});
</script>

<template>
    <div
        :data-tooltip-target="props.id"
        :data-tooltip-placement="props.placement"
        class="relative"
    >
        <slot></slot>
        <div
            :id="props.id"
            role="tooltip"
            class="absolute z-10 invisible inline-block max-w-xs px-3 py-2 text-sm text-center text-white transition-opacity duration-300 rounded-lg shadow-xs opacity-0 bg-primary tooltip dark:bg-white dark:text-gray-800 dark:shadow-lg text-nowrap"
        >
            <slot name="content"></slot>
            <div class="tooltip-arrow" data-popper-arrow></div>
        </div>
    </div>
</template>

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
        class="relative w-fit"
    >
        <slot></slot>
        <div
            :id="props.id"
            role="tooltip"
            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium rounded-lg shadow-xs opacity-0 text-white/90 bg-primary tooltip w-max"
        >
            <slot name="content"></slot>
            <div class="tooltip-arrow" data-popper-arrow></div>
        </div>
    </div>
</template>

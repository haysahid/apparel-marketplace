<script setup lang="ts">
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
    bgColor: {
        type: String,
        default: "bg-primary",
    },
    textColor: {
        type: String,
        default: "text-white/90",
    },
    bgColorHexCode: {
        type: String,
        default: null,
    },
    textColorHexCode: {
        type: String,
        default: null,
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
            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium rounded-lg shadow-xs opacity-0 tooltip w-max"
            :class="[props.bgColor, props.textColor]"
            :style="{
                backgroundColor: props.bgColorHexCode,
                color: props.textColorHexCode,
            }"
        >
            <slot name="content"></slot>
            <div class="tooltip-arrow" data-popper-arrow></div>
        </div>
    </div>
</template>

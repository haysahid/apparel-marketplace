<script setup>
import { computed } from "vue";

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    hexCode: {
        type: String,
        default: "#000000",
    },
    selected: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["click"]);

const radioStyle = computed(() => {
    return props.selected
        ? {
              outlineStyle: "solid",
              outlineWidth: "2px",
              outlineOffset: "-2px",
              outlineColor: props.hexCode,
              backgroundColor: props.hexCode,
          }
        : {
              outlineStyle: "solid",
              outlineWidth: "2px",
              outlineOffset: "-2px",
              outlineColor: props.hexCode,
          };
});

const onClick = () => {
    if (!props.disabled) {
        emit("click");
    }
};
</script>

<template>
    <div
        class="flex items-center justify-center gap-2 cursor-pointer px-3.5 py-2 sm:py-3 rounded-lg hover:bg-gray-50 outline outline-1 -outline-offset-1 outline-gray-400 min-w-[60px] h-fit"
        :class="{
            '!outline-2 !-outline-offset-2 outline-primary': props.selected,
            'opacity-30 hover:bg-transparent !cursor-default': props.disabled,
        }"
        @click="onClick"
    >
        <div
            class="rounded-full size-4 sm:size-5 shrink-0"
            :style="radioStyle"
        ></div>
        <span>{{ props.label }}</span>
    </div>
</template>

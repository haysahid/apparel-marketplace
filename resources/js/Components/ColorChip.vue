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
    radioClasses: {
        type: [String, Object, Array],
        default: "",
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
        class="flex items-center justify-center gap-2 cursor-pointer px-3.5 py-2 text-sm rounded-lg hover:bg-gray-50 outline outline-1 -outline-offset-1 outline-gray-400 min-w-[60px] h-fit"
        :class="{
            '!outline-2 !-outline-offset-2 outline-primary bg-primary/10 hover:bg-primary/10':
                props.selected,
            'opacity-30 hover:bg-transparent !cursor-default': props.disabled,
        }"
        @click="onClick"
    >
        <div
            class="rounded-full size-4 shrink-0"
            :class="props.radioClasses"
            :style="radioStyle"
        ></div>
        <span>{{ props.label }}</span>
        <slot name="suffix" />
    </div>
</template>

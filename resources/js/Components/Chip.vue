<script setup>
import { computed } from "vue";

const props = defineProps({
    label: {
        type: String,
        required: true,
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

const onClick = () => {
    if (!props.disabled) {
        emit("click");
    }
};
</script>

<template>
    <div
        class="flex items-center justify-center gap-2 cursor-pointer px-3.5 py-2 sm:py-3 rounded-lg hover:bg-gray-50 outline outline-1 -outline-offset-1 outline-gray-400 min-w-[60px] text-center"
        :class="{
            '!outline-2 !-outline-offset-2 !outline-primary bg-primary/10 hover:bg-primary/10':
                props.selected,
            'opacity-30 hover:bg-transparent !cursor-default': props.disabled,
        }"
        @click="onClick"
    >
        {{ props.label }}
    </div>
</template>

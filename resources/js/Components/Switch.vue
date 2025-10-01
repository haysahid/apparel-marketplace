<script setup lang="ts">
import { computed } from "vue";

const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue"]);

const isChecked = computed({
    get: () => props.modelValue,
    set: (value: boolean) => {
        if (!props.disabled) {
            emit("update:modelValue", value);
        }
    },
});

const toggle = () => {
    if (!props.disabled) {
        isChecked.value = !isChecked.value;
    }
};
</script>

<template>
    <button
        type="button"
        class="relative inline-flex flex-shrink-0 h-6 transition-colors duration-200 ease-in-out border-2 border-transparent rounded-full cursor-pointer w-11 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-light"
        :class="{
            'bg-primary': isChecked,
            'bg-gray-200': !isChecked,
            'opacity-50 cursor-not-allowed': disabled,
        }"
        role="switch"
        :aria-checked="isChecked"
        @click="toggle"
    >
        <span
            class="inline-block w-5 h-5 transition duration-200 ease-in-out transform bg-white rounded-full shadow pointer-events-none ring-0"
            :class="{
                'translate-x-5': isChecked,
                'translate-x-0': !isChecked,
            }"
        ></span>
    </button>
</template>

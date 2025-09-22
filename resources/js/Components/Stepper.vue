<script setup lang="ts">
import StepButton from "./StepButton.vue";

const props = defineProps({
    steps: {
        type: Array as () => {
            title: string;
            subtitle: string;
            disabled: boolean;
        }[],
        required: true,
    },
    stepIndex: {
        type: Number,
        required: true,
        default: 0,
    },
});

const emit = defineEmits(["update:stepIndex"]);

const updateStepIndex = (index: number) => {
    emit("update:stepIndex", index);
};
</script>

<template>
    <div
        class="flex flex-wrap items-center transition-all duration-300 ease-in-out"
    >
        <template v-for="(tab, index) in props.steps" :key="index">
            <StepButton
                :number="index + 1"
                :title="tab.title"
                :subtitle="tab.subtitle"
                :disabled="tab.disabled"
                :isActive="index == stepIndex"
                @click="updateStepIndex(index)"
            />
            <div
                v-if="index < props.steps.length - 1"
                class="flex-1 mx-2 border-t border-gray-200 max-w-16"
            ></div>
        </template>
    </div>
</template>

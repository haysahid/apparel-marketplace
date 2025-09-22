<script setup>
const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: null,
    },
    isActive: {
        type: Boolean,
        default: false,
    },
    number: {
        type: Number,
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <button
        type="button"
        class="flex items-center gap-2 py-3 text-sm font-medium transition-colors duration-200 text-start min-h-[50px]"
        :class="{
            'text-gray-700 dark:text-gray-300 ': !isActive,
            'text-primary dark:primary-dark ': isActive,
        }"
        :disabled="disabled"
    >
        <div
            v-if="number !== null"
            class="flex items-center justify-center w-6 h-6 text-xs font-semibold rounded-full"
            :class="{
                'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300':
                    !isActive,
                'bg-primary text-white dark:bg-primary-dark': isActive,
            }"
        >
            {{ number }}
        </div>
        <div class="flex flex-col items-start justify-center">
            <p class="font-semibold">{{ title }}</p>
            <p
                v-if="subtitle"
                class="text-xs text-gray-400 dark:text-gray-300"
                :class="{
                    'text-primary/80': isActive,
                }"
            >
                {{ subtitle }}
            </p>
        </div>
    </button>
</template>

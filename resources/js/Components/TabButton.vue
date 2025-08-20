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
});
</script>

<template>
    <button
        type="button"
        class="flex items-center gap-2 px-4 sm:px-6 py-3 text-sm font-medium transition-colors duration-200 hover:bg-gray-50 dark:hover:bg-gray-800 text-start min-h-[50px] w-full border-b-2 border-primary/80 sm:border-none"
        :class="{
            'text-gray-700 dark:text-gray-300 border-none': !isActive,
            'text-primary dark:primary-dark bg-primary-light/20 hover:bg-primary-light/20':
                isActive,
        }"
    >
        <slot name="leading" />
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

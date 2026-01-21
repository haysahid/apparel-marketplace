<script setup>
import InputError from "./InputError.vue";

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: null,
    },
    error: {
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
        class="px-4 sm:px-6 py-3 text-sm font-medium transition-colors duration-200 hover:bg-gray-50 dark:hover:bg-gray-800 text-start min-h-[50px] w-full border-b-2 border-primary/80 sm:border-none"
        :class="{
            'text-gray-700 dark:text-gray-300 border-none': !props.isActive,
            'text-primary dark:primary-dark bg-primary-light/20 hover:bg-primary-light/20':
                props.isActive,
        }"
    >
        <div class="flex items-center gap-2">
            <slot name="leading" />
            <div class="flex flex-col items-start justify-center">
                <p class="font-semibold">{{ props.title }}</p>
                <p
                    v-if="props.subtitle"
                    class="text-xs text-gray-400 dark:text-gray-300"
                    :class="{
                        'text-primary/80': props.isActive,
                    }"
                >
                    {{ props.subtitle }}
                </p>
            </div>
        </div>
        <InputError v-if="props.error" :message="props.error" class="mt-1" />
    </button>
</template>

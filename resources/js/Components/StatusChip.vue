<script setup>
const props = defineProps({
    status: {
        type: String,
        default: null,
    },
    label: {
        type: String,
        default: "",
    },
});
</script>

<template>
    <!-- Status -->
    <div
        class="flex items-center justify-center px-2 py-1 text-xs font-semibold text-gray-500 bg-gray-100 rounded-md h-fit"
        :class="{
            'text-gray-500 bg-gray-100': props.status === 'pending',
            'text-orange-500 bg-orange-100': props.status === 'paid',
            'text-indigo-500 bg-indigo-100': props.status === 'processing',
            'text-green-500 bg-green-100': props.status === 'completed',
            'text-red-500 bg-red-100':
                props.status === 'cancelled' || props.status === 'failed',
        }"
    >
        {{ props.label }}
    </div>
</template>

<script setup>
defineProps({
    type: {
        type: String,
        default: "submit",
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white transition duration-200 ease-in-out border border-transparent rounded-md shadow-sm bg-primary focus:bg-primary-dark active:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-50 hover:bg-primary-hover"
    >
        <slot name="prefix" />
        <slot />
        <slot name="suffix" />
    </button>
</template>

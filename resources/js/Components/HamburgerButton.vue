<script setup>
const props = defineProps({
    active: {
        type: Boolean,
        default: false,
    },
    invertColor: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <button
        class="inline-flex items-center justify-center p-2 transition duration-150 ease-in-out rounded-md text-gray-500/80 hover:text-gray-500 hover:bg-gray-500/10 focus:outline-none focus:bg-gray-500/10 focus:hover:bg-gray-500/20 focus:text-gray-500"
        :class="{
            'hover:bg-white/10 focus:bg-white/10 !text-white/80': invertColor,
        }"
        @click="$emit('toggle')"
    >
        <svg
            class="size-6"
            stroke="currentColor"
            fill="none"
            viewBox="0 0 24 24"
        >
            <path
                :class="{
                    hidden: active,
                    'inline-flex': !active,
                }"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
            />
            <path
                :class="{
                    hidden: !active,
                    'inline-flex': active,
                }"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
            />
        </svg>
    </button>
</template>

<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
});

const classes = computed(() => {
    return props.active
        ? "block w-full ps-6 pe-6 py-2 text-start text-sm font-medium text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100  transition duration-150 ease-in-out"
        : "block w-full ps-6 pe-6 py-2 text-start text-sm font-medium text-white/80 hover:text-white hover:bg-primary-light/10 focus:outline-none focus:text-gray-800 focus:bg-gray-50 transition duration-150 ease-in-out";
});
</script>

<template>
    <div>
        <button
            v-if="as == 'button'"
            :class="classes"
            class="w-full text-start"
        >
            <slot />
        </button>

        <a
            v-else-if="as == 'a'"
            :class="classes"
            class="w-full text-start"
            :href="href"
        >
            <slot />
        </a>

        <Link v-else :href="href" :class="classes">
            <slot />
        </Link>
    </div>
</template>

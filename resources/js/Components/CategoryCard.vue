<script setup>
const props = defineProps({
    name: String,
    image: String,
});
</script>

<template>
    <button
        type="button"
        class="flex flex-col items-center justify-center w-full gap-4 p-4 transition-all duration-300 ease-in-out bg-white border border-gray-200 rounded-2xl hover:ring-primary-light focus:outline-none hover:ring-2 focus:ring-2 focus:ring-primary-light aspect-square"
        @click="$emit('click')"
    >
        <img
            :src="props.image"
            :alt="props.name"
            class="object-contain w-full h-12 sm:h-16 md:h-20"
        />
        <p class="font-medium text-gray-700 sm:text-lg">
            {{ props.name }}
        </p>
    </button>
</template>

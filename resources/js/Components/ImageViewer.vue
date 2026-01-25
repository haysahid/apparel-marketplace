<script setup lang="ts">
const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    imageUrl: {
        type: String,
        required: false,
        default: null,
    },
});

const emit = defineEmits(["close"]);
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm"
        @click="emit('close')"
    >
        <button
            @click="emit('close')"
            class="absolute z-10 text-white transition-colors top-4 right-4 hover:text-gray-300"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                class="size-6"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>

        <Transition name="scale-fade" appear>
            <div class="w-full max-w-2xl">
                <div class="overflow-hidden bg-white" @click.stop>
                    <img
                        :src="$getImageUrl(props.imageUrl)"
                        :alt="props.name"
                        class="w-full h-auto object-contain max-h-[80vh]"
                    />
                </div>
            </div>
        </Transition>
    </div>
</template>

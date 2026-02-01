<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted, computed } from "vue";

const props = defineProps({
    items: {
        type: Array as () => CarouselItemModel[],
        required: true,
    },
});

const currentIndex = ref(0);
let intervalId: number | null = null;

function next() {
    currentIndex.value = (currentIndex.value + 1) % props.items.length;
}

function prev() {
    currentIndex.value =
        (currentIndex.value - 1 + props.items.length) % props.items.length;
}

function goTo(idx: number) {
    currentIndex.value = idx;
}

onMounted(() => {
    intervalId = setInterval(next, 8000);
});

onUnmounted(() => {
    clearInterval(intervalId);
});

const canGoToPreviousImage = computed(() => {
    return currentIndex.value > 0;
});

const canGoToNextImage = computed(() => {
    return currentIndex.value < props.items.length - 1;
});
</script>

<template>
    <div class="relative w-full overflow-hidden group">
        <div
            class="flex transition-transform duration-700"
            :style="{ transform: `translateX(-${currentIndex * 100}%)` }"
        >
            <div
                v-for="(item, idx) in props.items"
                :key="idx"
                class="relative flex items-center justify-center min-w-full overflow-hidden bg-gray-200 aspect-2/1"
            >
                <!-- Blurred background image -->
                <img
                    v-if="item.image"
                    :src="$getImageUrl(item.image)"
                    alt=""
                    aria-hidden="true"
                    class="absolute inset-0 z-0 object-cover w-full h-full scale-110 opacity-25 blur-md"
                    style="filter: blur(16px) brightness(0.8)"
                />
                <!-- Foreground image -->
                <Link
                    v-if="item.link"
                    :href="item.link || '#'"
                    class="z-10 w-full h-full"
                >
                    <img
                        :src="$getImageUrl(item.image)"
                        :alt="item.alt"
                        class="object-contain w-full h-full max-h-[70vh] relative z-10"
                    />
                </Link>
                <img
                    v-else
                    :src="$getImageUrl(item.image)"
                    :alt="item.alt"
                    class="object-contain w-full h-full max-h-[70vh] relative z-10"
                />

                <div
                    class="absolute z-20 px-4 py-2 text-white bg-black bg-opacity-50 rounded bottom-4 left-4"
                >
                    {{ item.caption }}
                </div>
            </div>
        </div>

        <button
            class="absolute p-2 text-gray-700 transition-all duration-300 ease-in-out transform -translate-y-1/2 bg-white rounded-full left-4 top-1/2 bg-opacity-70 hover:scale-105"
            @click="prev"
            aria-label="Previous"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                viewBox="0 0 20 20"
                class="size-5 sm:size-6"
            >
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M11.7803 5.21967C12.0732 5.51256 12.0732 5.98744 11.7803 6.28033L8.06066 10L11.7803 13.7197C12.0732 14.0126 12.0732 14.4874 11.7803 14.7803C11.4874 15.0732 11.0126 15.0732 10.7197 14.7803L6.46967 10.5303C6.17678 10.2374 6.17678 9.76256 6.46967 9.46967L10.7197 5.21967C11.0126 4.92678 11.4874 4.92678 11.7803 5.21967Z"
                    fill="currentColor"
                />
            </svg>
        </button>
        <button
            class="absolute p-2 text-gray-700 transition-colors duration-300 ease-in-out transform -translate-y-1/2 bg-white rounded-full right-4 top-1/2 bg-opacity-70 hover:scale-105"
            @click="next"
            aria-label="Next"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                viewBox="0 0 20 20"
                class="size-5 sm:size-6"
            >
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M8.21967 5.21967C8.51256 4.92678 8.98744 4.92678 9.28033 5.21967L13.5303 9.46967C13.8232 9.76256 13.8232 10.2374 13.5303 10.5303L9.28033 14.7803C8.98744 15.0732 8.51256 15.0732 8.21967 14.7803C7.92678 14.4874 7.92678 14.0126 8.21967 13.7197L11.9393 10L8.21967 6.28033C7.92678 5.98744 7.92678 5.51256 8.21967 5.21967Z"
                    fill="currentColor"
                />
            </svg>
        </button>

        <div
            class="absolute flex transform -translate-x-1/2 gap-x-3 bottom-4 left-1/2"
        >
            <span
                v-for="(slide, idx) in props.items"
                :key="idx"
                class="w-3 h-3 rounded-full"
                :class="currentIndex === idx ? 'bg-primary' : 'bg-gray-400'"
                @click="goTo(idx)"
            ></span>
        </div>
    </div>
</template>

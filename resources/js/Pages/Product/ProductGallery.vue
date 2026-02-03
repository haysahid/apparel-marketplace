<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { getImageUrl } from "@/plugins/helpers";

const props = defineProps({
    images: {
        type: Array as () => ProductVariantImageEntity[],
        required: true,
    },
    altText: {
        type: String,
        default: "Product Image",
    },
});

const image = ref(props.images.length > 0 ? props.images[0] : null);

const imageIndex = ref(0);

watch(
    () => props.images,
    (newImages) => {
        if (props.images.length > 0 && props.images[0] !== image.value) {
            image.value = newImages[0];
            imageIndex.value = 0;
        }
    },
);

const changeImage = (index) => {
    image.value = props.images[index];
    imageIndex.value = index;
};

const canGoToPreviousImage = computed(() => {
    return imageIndex.value > 0;
});

const canGoToNextImage = computed(() => {
    return imageIndex.value < props.images.length - 1;
});

const goToPreviousImage = () => {
    if (canGoToPreviousImage.value) {
        imageIndex.value--;
        image.value = props.images[imageIndex.value];
    }
};

const goToNextImage = () => {
    if (canGoToNextImage.value) {
        imageIndex.value++;
        image.value = props.images[imageIndex.value];
    }
};
</script>

<template>
    <div
        v-if="props.images.length > 0"
        class="flex flex-col items-start justify-start"
    >
        <!-- Main Images -->
        <div
            class="relative flex items-center justify-center w-full overflow-hidden transition-all duration-300 ease-in-out border border-gray-100 rounded-lg group"
        >
            <img
                :src="getImageUrl(image.original_url)"
                :alt="props.altText"
                class="object-contain w-full transition-all duration-300 ease-in-out rounded-lg aspect-square group-hover:scale-105"
            />
            <button
                v-if="canGoToPreviousImage"
                @click="goToPreviousImage"
                type="button"
                class="absolute flex items-center justify-center font-semibold transition-all duration-300 ease-in-out rounded-full opacity-0 size-8 sm:size-10 aspect-square text-primary hover:bg-primary-light/80 -left-8 group-hover:bg-black/40 group-hover:opacity-100 group-hover:left-4 hover:scale-110"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 20 20"
                    class="fill-gray-200 size-5 sm:size-6"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M11.7803 5.21967C12.0732 5.51256 12.0732 5.98744 11.7803 6.28033L8.06066 10L11.7803 13.7197C12.0732 14.0126 12.0732 14.4874 11.7803 14.7803C11.4874 15.0732 11.0126 15.0732 10.7197 14.7803L6.46967 10.5303C6.17678 10.2374 6.17678 9.76256 6.46967 9.46967L10.7197 5.21967C11.0126 4.92678 11.4874 4.92678 11.7803 5.21967Z"
                    />
                </svg>
            </button>
            <button
                v-if="canGoToNextImage"
                @click="goToNextImage"
                type="button"
                class="absolute flex items-center justify-center font-semibold transition-all duration-300 ease-in-out rounded-full opacity-0 size-8 sm:size-10 aspect-square text-primary hover:bg-primary-light/80 -right-8 group-hover:bg-black/40 group-hover:opacity-100 group-hover:right-4 hover:scale-110"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 20 20"
                    class="fill-gray-200 size-5 sm:size-6"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M8.21967 5.21967C8.51256 4.92678 8.98744 4.92678 9.28033 5.21967L13.5303 9.46967C13.8232 9.76256 13.8232 10.2374 13.5303 10.5303L9.28033 14.7803C8.98744 15.0732 8.51256 15.0732 8.21967 14.7803C7.92678 14.4874 7.92678 14.0126 8.21967 13.7197L11.9393 10L8.21967 6.28033C7.92678 5.98744 7.92678 5.51256 8.21967 5.21967Z"
                    />
                </svg>
            </button>
            <div
                class="absolute px-2 py-1 text-sm text-white transition-opacity duration-300 ease-in-out transform -translate-x-1/2 rounded-full opacity-0 bottom-2 left-1/2 bg-black/50 group-hover:opacity-100"
            >
                {{ imageIndex + 1 }} / {{ props.images.length }}
            </div>
        </div>

        <!-- Other Images -->
        <div class="flex items-center gap-2 py-4 overflow-x-auto">
            <img
                v-for="(img, index) in props.images"
                :key="index"
                :src="getImageUrl(img.preview_url)"
                :alt="props.altText"
                class="h-[50px] aspect-[3/2] object-cover rounded cursor-pointer transition duration-200 hover:scale-105 m-0.5"
                :class="{
                    '!cursor-default hover:!scale-100 outline outline-2 outline-primary':
                        image.original_url == img.original_url,
                }"
                @click="changeImage(index)"
            />
        </div>
    </div>
    <div
        v-else
        class="flex items-center justify-center w-full bg-gray-100 rounded-2xl aspect-square"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            class="size-12 fill-gray-400"
        >
            <path
                d="M5 21C4.45 21 3.97933 20.8043 3.588 20.413C3.19667 20.0217 3.00067 19.5507 3 19V5C3 4.45 3.196 3.97933 3.588 3.588C3.98 3.19667 4.45067 3.00067 5 3H19C19.55 3 20.021 3.196 20.413 3.588C20.805 3.98 21.0007 4.45067 21 5V19C21 19.55 20.8043 20.021 20.413 20.413C20.0217 20.805 19.5507 21.0007 19 21H5ZM6 17H18L14.25 12L11.25 16L9 13L6 17Z"
            />
        </svg>
    </div>
</template>

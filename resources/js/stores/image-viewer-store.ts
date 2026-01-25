import { defineStore } from "pinia";
import { ref } from "vue";

export const useImageViewerStore = defineStore("image_viewer", () => {
    const selectedImage = ref<TemporaryMediaEntity | MediaEntity | null>(null);

    return {
        selectedImage,
    };
});

<script setup lang="ts">
import mediaService from "@/services/my-store/media-service";
import { ref } from "vue";
import DefaultCard from "./DefaultCard.vue";
import { AxiosProgressEvent } from "axios";
import PrimaryButton from "./PrimaryButton.vue";
import { useDialogStore } from "@/stores/dialog-store";

const props = defineProps({
    modelType: {
        type: String,
        required: false,
        default: "product",
    },
    fileType: {
        type: String,
        required: false,
        default: "image",
    },
});

const mediaList = ref<PaginationModel<any>>(null);

mediaService().getMediaList({
    modelType: props.modelType,
});

const selectedFiles = ref<FileList | null>(null);
const uploadListProgress = ref<AxiosProgressEvent[]>([]);
const totalUploadProgress = ref(0);
const uploadStatus = ref<"idle" | "uploading" | "completed" | "error">("idle");

const getObjectURL = (file: File) => {
    return URL.createObjectURL(file);
};

const revokeObjectURL = (url: string) => {
    URL.revokeObjectURL(url);
};

const removeFile = (index: number) => {
    if (selectedFiles.value) {
        const fileArray = Array.from(selectedFiles.value);
        fileArray.splice(index, 1);
        const dataTransfer = new DataTransfer();
        fileArray.forEach((file) => dataTransfer.items.add(file));
        selectedFiles.value = dataTransfer.files;
    }
};

const addFiles = (files: FileList) => {
    if (selectedFiles.value) {
        const fileArray = Array.from(selectedFiles.value);
        fileArray.unshift(...Array.from(files));
        const dataTransfer = new DataTransfer();
        fileArray.forEach((file) => dataTransfer.items.add(file));
        selectedFiles.value = dataTransfer.files;
    } else {
        selectedFiles.value = files;
    }
};

const uploadFiles = async () => {
    if (selectedFiles.value) {
        uploadStatus.value = "uploading";

        await mediaService().uploadMediaBulk(
            {
                modelType: props.modelType,
                modelId: 0,
                files: Array.from(selectedFiles.value),
            },
            (progressEvent: AxiosProgressEvent, index: number) => {
                uploadListProgress.value[index] = progressEvent;
                const totalLoaded = uploadListProgress.value.reduce(
                    (acc, curr) => acc + (curr.loaded || 0),
                    0,
                );
                const totalTotal = uploadListProgress.value.reduce(
                    (acc, curr) => acc + (curr.total || 0),
                    0,
                );
                totalUploadProgress.value =
                    totalTotal > 0 ? (totalLoaded / totalTotal) * 100 : 0;
            },
        );

        if (totalUploadProgress.value === 100) {
            uploadStatus.value = "completed";
            // Clear selected files after upload
            selectedFiles.value = null;
            uploadListProgress.value = [];
            totalUploadProgress.value = 0;

            useDialogStore().openSuccessDialog("Files uploaded successfully.");
        }
    }
};
</script>

<template>
    <DefaultCard isMain>
        <h2 class="mb-4 text-lg font-semibold">Media List</h2>

        <pre>{{ mediaList }}</pre>

        <!-- Upload Section -->
        <div class="mt-6">
            <h3 class="mb-2 font-medium">Upload Media</h3>
            <label
                class="flex flex-col items-center justify-center w-full h-32 px-4 transition bg-white border-2 border-dashed rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50"
            >
                <svg
                    class="w-10 h-10 mb-2 text-blue-400"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 4v16m8-8H4"
                    />
                </svg>
                <span class="text-sm text-gray-600">
                    Click to select files or drag & drop
                </span>
                <input
                    type="file"
                    :accept="props.fileType === 'image' ? 'image/*' : '*/*'"
                    multiple
                    class="hidden"
                    @change="
                        (event) => {
                            const files = (event.target as HTMLInputElement)
                                .files;
                            if (files) {
                                addFiles(files);
                            }
                        }
                    "
                />
            </label>

            <!-- Selected Files  -->
            <div v-if="selectedFiles" class="mt-4">
                <div class="flex items-center justify-between gap-4 mb-2">
                    <h4 class="font-medium">Selected Files</h4>
                    <button
                        type="button"
                        class="text-sm text-red-500 hover:underline"
                        @click="selectedFiles = null"
                    >
                        Clear All
                    </button>
                </div>
                <ul class="list-none border rounded-lg">
                    <li
                        v-for="(file, index) in Array.from(selectedFiles)"
                        :key="index"
                    >
                        <div
                            class="flex items-center gap-4 p-2 min-h-14"
                            :class="{
                                'border-b':
                                    index !=
                                    Array.from(selectedFiles).length - 1,
                            }"
                        >
                            <div
                                v-if="
                                    props.fileType === 'image' &&
                                    file.type.startsWith('image/')
                                "
                            >
                                <img
                                    :src="getObjectURL(file)"
                                    alt="preview"
                                    class="object-cover w-16 rounded aspect-4/3"
                                    @load="
                                        ($event) => {
                                            revokeObjectURL(
                                                (
                                                    $event.target as HTMLImageElement
                                                ).src,
                                            );
                                        }
                                    "
                                />
                            </div>
                            <div>
                                <p class="text-sm">{{ file.name }}</p>
                                <p class="text-xs text-gray-600">
                                    {{ (file.size / 1024).toFixed(2) }} KB
                                </p>

                                <!-- Progress Bar -->
                                <div
                                    v-if="uploadStatus == 'uploading'"
                                    class="w-48 h-2 mt-2 bg-gray-200 rounded"
                                >
                                    <div
                                        class="h-2 bg-blue-500 rounded"
                                        :style="{
                                            width:
                                                uploadListProgress[index] &&
                                                uploadListProgress[index].total
                                                    ? ((uploadListProgress[
                                                          index
                                                      ].loaded || 0) /
                                                          (uploadListProgress[
                                                              index
                                                          ].total || 1)) *
                                                          100 +
                                                      '%'
                                                    : '0%',
                                        }"
                                    ></div>
                                </div>
                            </div>

                            <!-- Delete Button -->
                            <div
                                v-if="uploadStatus != 'uploading'"
                                class="ml-auto"
                            >
                                <button
                                    type="button"
                                    class="p-[7px] text-gray-400 bg-white rounded-full hover:bg-gray-100 transition-all duration-300 ease-in-out"
                                    @click="removeFile(index)"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="size-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Upload Button -->
            <div v-if="selectedFiles" class="mt-4">
                <PrimaryButton @click="uploadFiles">
                    Upload Files
                </PrimaryButton>
            </div>
        </div>
    </DefaultCard>
</template>

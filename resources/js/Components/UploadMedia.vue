<script setup lang="ts">
import mediaService from "@/services/my-store/media-service";
import { useDialogStore } from "@/stores/dialog-store";
import { AxiosProgressEvent } from "axios";
import { ref } from "vue";
import PrimaryButton from "./PrimaryButton.vue";

const props = defineProps({
    modelType: {
        type: String,
        required: true,
    },
    modelId: {
        type: Number,
        default: null,
    },
    fileType: {
        type: String,
        default: "all",
    },
});

const emit = defineEmits(["uploadCompleted"]);

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

        if (props.modelId) {
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
        } else {
            await mediaService().uploadTemporaryMediaBulk(
                Array.from(selectedFiles.value),
                {
                    onProgress: (
                        progressEvent: AxiosProgressEvent,
                        index: number,
                    ) => {
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
                            totalTotal > 0
                                ? (totalLoaded / totalTotal) * 100
                                : 0;
                    },
                    onSuccess: (
                        uploadedTemporaryMediaList: TemporaryMediaEntity[],
                    ) => {
                        uploadStatus.value = "completed";
                        // Clear selected files after upload
                        selectedFiles.value = null;
                        uploadListProgress.value = [];
                        totalUploadProgress.value = 0;

                        emit("uploadCompleted", uploadedTemporaryMediaList);

                        useDialogStore().openSuccessDialog(
                            "Files uploaded successfully.",
                        );
                    },
                },
            );
        }
    }
};

defineExpose({
    selectedFiles,
    uploadFiles,
});
</script>

<template>
    <div class="flex flex-col items-start">
        <h2 class="mb-4 text-base font-semibold">Unggah Media</h2>
        <label
            class="flex flex-col items-center justify-center w-full h-48 px-4 transition duration-300 ease-in-out bg-white border-2 border-dashed rounded-lg cursor-pointer hover:border-primary hover:bg-primary-light/20 group"
            @dragover.prevent
            @drop.prevent="
                (event) => {
                    const files = event.dataTransfer?.files;
                    if (files && files.length > 0) {
                        addFiles(files);
                    }
                }
            "
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                class="mb-2 text-gray-400 transition-colors duration-300 ease-in-out size-10 group-hover:text-primary"
            >
                <path
                    d="M11 20H6.5C4.98333 20 3.68767 19.475 2.613 18.425C1.53833 17.375 1.00067 16.0917 1 14.575C1 13.275 1.39167 12.1167 2.175 11.1C2.95833 10.0833 3.98333 9.43333 5.25 9.15C5.66667 7.61667 6.5 6.375 7.75 5.425C9 4.475 10.4167 4 12 4C13.95 4 15.6043 4.67933 16.963 6.038C18.3217 7.39667 19.0007 9.05067 19 11C20.15 11.1333 21.1043 11.6293 21.863 12.488C22.6217 13.3467 23.0007 14.3507 23 15.5C23 16.75 22.5627 17.8127 21.688 18.688C20.8133 19.5633 19.7507 20.0007 18.5 20H13V12.85L14.6 14.4L16 13L12 9L8 13L9.4 14.4L11 12.85V20Z"
                    fill="currentColor"
                />
            </svg>
            <span
                class="text-sm text-gray-600 transition-colors duration-300 ease-in-out group-hover:text-primary-dark"
            >
                Klik atau seret file ke sini untuk mengunggah
            </span>
            <input
                type="file"
                :accept="props.fileType === 'image' ? 'image/*' : '*/*'"
                multiple
                class="hidden"
                @change="
                    (event) => {
                        const files = (event.target as HTMLInputElement).files;
                        if (files) {
                            addFiles(files);
                        }
                    }
                "
            />
        </label>

        <!-- Selected Files  -->
        <div v-if="selectedFiles" class="w-full mt-4">
            <div class="flex items-center justify-between gap-4 mb-2">
                <h4 class="font-medium">Selected Files</h4>
                <button
                    type="button"
                    class="text-sm text-red-500 hover:underline"
                    @click="selectedFiles = null"
                >
                    Hapus Semua
                </button>
            </div>
            <ul class="list-none border rounded-lg">
                <li
                    v-for="(file, index) in Array.from(selectedFiles)"
                    :key="index"
                >
                    <Transition name="fade" appear>
                        <div
                            class="flex items-center gap-4 p-2"
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
                                    class="object-cover w-16 rounded aspect-4/3 h-14"
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
                            <div class="flex flex-col items-start">
                                <p class="text-sm">{{ file.name }}</p>
                                <p class="text-xs text-gray-600">
                                    {{ (file.size / 1024).toFixed(2) }}
                                    KB
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
                    </Transition>
                </li>
            </ul>
        </div>
    </div>
</template>

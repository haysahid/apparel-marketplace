<script setup lang="ts">
import mediaService from "@/services/my-store/media-service";
import { useDialogStore } from "@/stores/dialog-store";
import axios, { AxiosProgressEvent, CancelToken } from "axios";
import { computed, ref } from "vue";
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
    autoUpload: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["uploadCompleted"]);

const selectedFiles = ref<FileList | null>(null);
const uploadListProgress = ref<AxiosProgressEvent[]>([]);
const abortControllers = ref<AbortController[]>([]);

const totalUploadProgress = ref(0);
const uploadStatus = ref<"idle" | "uploading" | "completed" | "error">("idle");

const getObjectURL = (file: File) => {
    return URL.createObjectURL(file);
};

const revokeObjectURL = (url: string) => {
    URL.revokeObjectURL(url);
};

const addFiles = async (files: FileList) => {
    const newFiles = Array.from(files);

    if (selectedFiles.value) {
        const fileArray = Array.from(selectedFiles.value);
        fileArray.unshift(...newFiles);
        const dataTransfer = new DataTransfer();
        fileArray.forEach((file) => dataTransfer.items.add(file));
        selectedFiles.value = dataTransfer.files;
    } else {
        selectedFiles.value = files;
    }

    // Add a CancelToken for each new file
    newFiles.forEach(() => {
        // You may need to import axios if not already
        const cancelToken = new AbortController();
        abortControllers.value.push(cancelToken);
    });

    if (props.autoUpload) {
        // Delay to ensure selectedFiles is updated
        await new Promise((resolve) => setTimeout(resolve, 600));
        uploadFiles();
    }
};

const removeFile = (index: number) => {
    if (selectedFiles.value) {
        const fileArray = Array.from(selectedFiles.value);
        fileArray.splice(index, 1);
        const dataTransfer = new DataTransfer();
        fileArray.forEach((file) => dataTransfer.items.add(file));
        selectedFiles.value = dataTransfer.files;

        // Also remove corresponding progress and cancel token
        uploadListProgress.value.splice(index, 1);
        abortControllers.value.splice(index, 1);
    }
};

const clearAllFiles = () => {
    selectedFiles.value = null;
    uploadListProgress.value = [];
    abortControllers.value = [];
    totalUploadProgress.value = 0;
    uploadStatus.value = "idle";
};

const ignoreIndexes = computed<number[]>(() => {
    const indexes: number[] = [];
    uploadListProgress.value.forEach((progress, index) => {
        if (
            progress.loaded === progress.total &&
            progress.total !== undefined
        ) {
            indexes.push(index);
        }
    });
    return indexes;
});

const uploadFiles = async () => {
    if (selectedFiles.value) {
        uploadStatus.value = "uploading";

        if (props.modelId) {
            await mediaService().uploadMediaBulk(
                {
                    modelType: props.modelType,
                    modelId: 0,
                    files: Array.from(selectedFiles.value),
                    abortControllers: abortControllers.value,
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
                {
                    files: Array.from(selectedFiles.value),
                    abortControllers: abortControllers.value,
                    ignoreIndexes: ignoreIndexes.value,
                },
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
                        emit("uploadCompleted", uploadedTemporaryMediaList);

                        // // Delay to allow user to see completed status
                        // setTimeout(() => {
                        //     // Clear selected files after upload
                        //     selectedFiles.value = null;
                        //     uploadListProgress.value = [];
                        //     totalUploadProgress.value = 0;
                        //     uploadStatus.value = "idle";
                        //     emit("uploadCompleted", uploadedTemporaryMediaList);
                        // }, 1000);
                    },
                },
            );
        }
    }
};

const uploadFile = async (file: File, index: number) => {
    // Reset abortController for this file
    const abortController = new AbortController();
    abortControllers.value[index] = abortController;

    uploadStatus.value = "uploading";

    if (props.modelId) {
        await mediaService().uploadMedia(
            {
                modelType: props.modelType,
                modelId: props.modelId,
                file: file,
            },
            {
                autoShowDialog: false,
                onProgress: (progressEvent: AxiosProgressEvent) => {
                    uploadListProgress.value[index] = progressEvent;
                },
                onSuccess: (uploadedMedia: MediaEntity) => {
                    uploadStatus.value = "completed";
                    emit("uploadCompleted", [uploadedMedia]);
                },
            },
        );
    } else {
        await mediaService().uploadTemporaryMedia(
            {
                file,
            },
            {
                autoShowDialog: false,
                onProgress: (progressEvent: AxiosProgressEvent) => {
                    uploadListProgress.value[index] = progressEvent;
                },
                onSuccess: (uploadedTemporaryMedia: TemporaryMediaEntity) => {
                    uploadStatus.value = "completed";
                    emit("uploadCompleted", [uploadedTemporaryMedia]);
                },
            },
        );
    }
};

defineExpose({
    selectedFiles,
    uploadFiles,
    uploadStatus,
});
</script>

<template>
    <div class="flex flex-col items-start h-full">
        <h2 class="mb-4 text-base font-semibold">Unggah Media</h2>

        <!-- Upload Area -->
        <label
            class="flex flex-col items-center justify-center w-full h-full px-4 transition duration-300 ease-in-out bg-white border-2 border-dashed rounded-lg cursor-pointer min-h-48 hover:border-primary hover:bg-primary-light/20 group"
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
                <h4 class="font-medium">
                    File Terpilih ({{ Array.from(selectedFiles).length }})
                </h4>
                <button
                    v-if="
                        uploadListProgress.some(
                            (progress) => progress.loaded < progress.total,
                        )
                    "
                    type="button"
                    class="text-sm text-red-500 hover:underline"
                    @click="clearAllFiles()"
                >
                    Batalkan Semua
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
                            <div class="flex flex-col items-start w-full">
                                <p class="text-sm">{{ file.name }}</p>
                                <p class="text-xs text-gray-600">
                                    {{ (file.size / 1024).toFixed(2) }}
                                    KB
                                </p>

                                <!-- Progress Bar -->
                                <div
                                    v-if="
                                        uploadListProgress[index]?.loaded <
                                        uploadListProgress[index]?.total
                                    "
                                    class="w-full h-1.5 mt-2 bg-gray-200 rounded-full"
                                >
                                    <div
                                        class="h-1.5 bg-blue-500 rounded"
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
                            <div class="">
                                <!-- Success Icon -->
                                <div
                                    v-if="
                                        uploadListProgress[index] &&
                                        uploadListProgress[index]?.loaded ===
                                            uploadListProgress[index]?.total
                                    "
                                    class="p-[7px]"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        class="text-green-500 size-5"
                                    >
                                        <path
                                            d="M10.6 13.8L8.45 11.65C8.26667 11.4667 8.03333 11.375 7.75 11.375C7.46667 11.375 7.23333 11.4667 7.05 11.65C6.86667 11.8333 6.775 12.0667 6.775 12.35C6.775 12.6333 6.86667 12.8667 7.05 13.05L9.9 15.9C10.1 16.1 10.3333 16.2 10.6 16.2C10.8667 16.2 11.1 16.1 11.3 15.9L16.95 10.25C17.1333 10.0667 17.225 9.83333 17.225 9.55C17.225 9.26667 17.1333 9.03333 16.95 8.85C16.7667 8.66667 16.5333 8.575 16.25 8.575C15.9667 8.575 15.7333 8.66667 15.55 8.85L10.6 13.8ZM12 22C10.6167 22 9.31667 21.7373 8.1 21.212C6.88334 20.6867 5.825 19.9743 4.925 19.075C4.025 18.1757 3.31267 17.1173 2.788 15.9C2.26333 14.6827 2.00067 13.3827 2 12C1.99933 10.6173 2.262 9.31733 2.788 8.1C3.314 6.88267 4.02633 5.82433 4.925 4.925C5.82367 4.02567 6.882 3.31333 8.1 2.788C9.318 2.26267 10.618 2 12 2C13.382 2 14.682 2.26267 15.9 2.788C17.118 3.31333 18.1763 4.02567 19.075 4.925C19.9737 5.82433 20.6863 6.88267 21.213 8.1C21.7397 9.31733 22.002 10.6173 22 12C21.998 13.3827 21.7353 14.6827 21.212 15.9C20.6887 17.1173 19.9763 18.1757 19.075 19.075C18.1737 19.9743 17.1153 20.687 15.9 21.213C14.6847 21.739 13.3847 22.0013 12 22Z"
                                            fill="currentColor"
                                        />
                                    </svg>
                                </div>

                                <!-- Retry Button -->
                                <button
                                    v-else-if="
                                        uploadListProgress[index] &&
                                        uploadListProgress[index]?.loaded <=
                                            uploadListProgress[index]?.total &&
                                        abortControllers[index]?.signal.aborted
                                    "
                                    type="button"
                                    class="p-[7px] text-blue-500 bg-white rounded-full hover:bg-gray-100 transition-all duration-300 ease-in-out"
                                    @click="
                                        () => {
                                            uploadFile(file, index);
                                        }
                                    "
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                    >
                                        <path
                                            d="M12 20C9.76667 20 7.875 19.225 6.325 17.675C4.775 16.125 4 14.2333 4 12C4 9.76667 4.775 7.875 6.325 6.325C7.875 4.775 9.76667 4 12 4C13.15 4 14.25 4.23734 15.3 4.712C16.35 5.18667 17.25 5.866 18 6.75V5C18 4.71667 18.096 4.47934 18.288 4.288C18.48 4.09667 18.7173 4.00067 19 4C19.2827 3.99934 19.5203 4.09534 19.713 4.288C19.9057 4.48067 20.0013 4.718 20 5V10C20 10.2833 19.904 10.521 19.712 10.713C19.52 10.905 19.2827 11.0007 19 11H14C13.7167 11 13.4793 10.904 13.288 10.712C13.0967 10.52 13.0007 10.2827 13 10C12.9993 9.71734 13.0953 9.48 13.288 9.288C13.4807 9.096 13.718 9 14 9H17.2C16.6667 8.06667 15.9377 7.33334 15.013 6.8C14.0883 6.26667 13.084 6 12 6C10.3333 6 8.91667 6.58334 7.75 7.75C6.58333 8.91667 6 10.3333 6 12C6 13.6667 6.58333 15.0833 7.75 16.25C8.91667 17.4167 10.3333 18 12 18C13.1333 18 14.171 17.7127 15.113 17.138C16.055 16.5633 16.784 15.7923 17.3 14.825C17.4333 14.5917 17.621 14.4293 17.863 14.338C18.105 14.2467 18.3507 14.2423 18.6 14.325C18.8667 14.4083 19.0583 14.5833 19.175 14.85C19.2917 15.1167 19.2833 15.3667 19.15 15.6C18.4667 16.9333 17.4917 18 16.225 18.8C14.9583 19.6 13.55 20 12 20Z"
                                            fill="currentColor"
                                        />
                                    </svg>
                                </button>

                                <button
                                    v-else
                                    type="button"
                                    class="p-[7px] text-gray-400 bg-white rounded-full hover:bg-gray-100 transition-all duration-300 ease-in-out"
                                    @click="
                                        () => {
                                            if (uploadStatus != 'uploading') {
                                                removeFile(index);
                                            } else if (
                                                uploadListProgress[index]
                                                    .loaded <
                                                uploadListProgress[index].total
                                            ) {
                                                // Cancel upload if in progress
                                                abortControllers[index].abort();
                                            }
                                        }
                                    "
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

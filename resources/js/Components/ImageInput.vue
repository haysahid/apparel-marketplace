<script setup>
import { ref } from "vue";
import InputError from "@/Components/InputError.vue";
import { getImageUrl } from "@/plugins/helpers.js";

const props = defineProps([
    "id",
    "type",
    "placeholder",
    "modelValue",
    "warning",
    "disabled",
    "autoFocus",
    "width",
    "height",
    "objectFit",
    "error",
    "isDragging",
    "showDeleteButton",
]);

const emit = defineEmits(["update:modelValue", "enter", "delete"]);

const imagePath = ref(null);

if (props.modelValue instanceof File) {
    imagePath.value = URL.createObjectURL(props.modelValue);
}

var loadFile = function (event) {
    var input = event.target;
    var file = input.files[0];

    imagePath.value = URL.createObjectURL(event.target.files[0]);

    emit("update:modelValue", file);
};

function clearImage() {
    imagePath.value = null;
}

defineExpose({ clearImage });
</script>

<template>
    <div class="w-full">
        <label
            :for="props.id"
            class="block w-full group"
            :class="[props.width]"
        >
            <div class="cursor-pointer shrink-0">
                <!-- Preview -->
                <div
                    v-if="imagePath || props.modelValue"
                    class="relative w-full"
                    :class="[props.height, props.width]"
                >
                    <img
                        id="preview_img"
                        class="w-full transition-all duration-300 ease-in-out border border-gray-200 rounded-lg shadow-sm aspect-square dark:border-gray-600"
                        :class="[
                            {
                                'outline outline-2 outline-red-500':
                                    props.error,
                            },
                            props.height,
                            props.objectFit ? props.objectFit : 'object-cover',
                            props.width,
                        ]"
                        :src="imagePath || getImageUrl(props.modelValue)"
                        alt="Current image"
                    />
                    <div
                        v-if="!props.isDragging"
                        class="absolute inset-0 flex flex-col items-center justify-center w-full h-full gap-1 p-2 duration-300 ease-linear bg-black bg-opacity-0 rounded-lg group-hover:bg-opacity-70"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="text-gray-400 text-opacity-0 duration-300 ease-linear dark:text-opacity-0 size-6 dark:text-gray-400 group-hover:text-gray-300 dark:group-hover:text-gray-300 group-hover:text-opacity-100 dark:group-hover:text-opacity-100"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"
                            />
                        </svg>

                        <p
                            class="text-sm text-center text-gray-400 text-opacity-0 duration-300 ease-linear dark:text-opacity-0 dark:text-gray-400 group-hover:text-gray-300 dark:group-hover:text-gray-300 group-hover:text-opacity-100 dark:group-hover:text-opacity-100"
                        >
                            Ganti gambar
                        </p>
                    </div>

                    <!-- Delete button -->
                    <button
                        v-if="props.showDeleteButton"
                        type="button"
                        class="absolute p-1 text-gray-300 transition-all duration-300 ease-in-out rounded-full opacity-0 bg-gray-300/0 top-1 right-1 hover:bg-gray-300/20 hover:text-gray-300 group-hover:text-gray-300 group-hover:opacity-100"
                        @click.prevent="
                            clearImage();
                            emit('delete');
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

                <!-- Placeholder -->
                <div
                    v-else
                    class="flex items-center justify-center w-full h-32 text-gray-400 duration-300 border border-gray-200 rounded-lg shadow-sm group-hover:bg-gray-300/20 dark:bg-form-input dark:group-hover:bg-form-input/10 dark:group-hover:border-gray-600 dark:border-gray-600"
                    :class="[props.height, props.width]"
                >
                    <div class="flex flex-col items-center gap-1 p-2">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="36"
                            height="37"
                            viewBox="0 0 36 37"
                            class="fill-gray-400 size-6 dark:fill-gray-400"
                        >
                            <path
                                d="M28.5 20.0003C28.1022 20.0003 27.7206 20.1584 27.4393 20.4397C27.158 20.721 27 21.1025 27 21.5003V22.0703L24.78 19.8503C23.9961 19.0726 22.9367 18.6363 21.8325 18.6363C20.7283 18.6363 19.6689 19.0726 18.885 19.8503L17.835 20.9003L14.115 17.1803C13.3202 16.4237 12.2648 16.0018 11.1675 16.0018C10.0702 16.0018 9.01482 16.4237 8.22 17.1803L6 19.4003V11.0003C6 10.6025 6.15804 10.221 6.43934 9.93967C6.72064 9.65837 7.10218 9.50033 7.5 9.50033H18C18.3978 9.50033 18.7794 9.3423 19.0607 9.06099C19.342 8.77969 19.5 8.39816 19.5 8.00033C19.5 7.60251 19.342 7.22098 19.0607 6.93967C18.7794 6.65837 18.3978 6.50033 18 6.50033H7.5C6.30653 6.50033 5.16193 6.97444 4.31802 7.81835C3.47411 8.66227 3 9.80686 3 11.0003V29.0003C3 30.1938 3.47411 31.3384 4.31802 32.1823C5.16193 33.0262 6.30653 33.5003 7.5 33.5003H25.5C26.6935 33.5003 27.8381 33.0262 28.682 32.1823C29.5259 31.3384 30 30.1938 30 29.0003V21.5003C30 21.1025 29.842 20.721 29.5607 20.4397C29.2794 20.1584 28.8978 20.0003 28.5 20.0003ZM7.5 30.5003C7.10218 30.5003 6.72064 30.3423 6.43934 30.061C6.15804 29.7797 6 29.3982 6 29.0003V23.6453L10.35 19.2953C10.5704 19.0853 10.8631 18.9682 11.1675 18.9682C11.4719 18.9682 11.7646 19.0853 11.985 19.2953L16.74 24.0503L23.19 30.5003H7.5ZM27 29.0003C26.9968 29.2873 26.9022 29.5658 26.73 29.7953L19.965 23.0003L21.015 21.9503C21.1225 21.8406 21.2509 21.7534 21.3926 21.6938C21.5342 21.6343 21.6863 21.6037 21.84 21.6037C21.9937 21.6037 22.1458 21.6343 22.2874 21.6938C22.4291 21.7534 22.5575 21.8406 22.665 21.9503L27 26.3153V29.0003ZM34.065 6.93533L29.565 2.43533C29.4223 2.29877 29.2541 2.19172 29.07 2.12033C28.7048 1.97031 28.2952 1.97031 27.93 2.12033C27.7459 2.19172 27.5777 2.29877 27.435 2.43533L22.935 6.93533C22.6525 7.21779 22.4939 7.60088 22.4939 8.00033C22.4939 8.39978 22.6525 8.78288 22.935 9.06533C23.2175 9.34779 23.6005 9.50647 24 9.50647C24.3995 9.50647 24.7825 9.34779 25.065 9.06533L27 7.11533V15.5003C27 15.8982 27.158 16.2797 27.4393 16.561C27.7206 16.8423 28.1022 17.0003 28.5 17.0003C28.8978 17.0003 29.2794 16.8423 29.5607 16.561C29.842 16.2797 30 15.8982 30 15.5003V7.11533L31.935 9.06533C32.0744 9.20592 32.2403 9.31752 32.4231 9.39367C32.6059 9.46982 32.802 9.50903 33 9.50903C33.198 9.50903 33.3941 9.46982 33.5769 9.39367C33.7597 9.31752 33.9256 9.20592 34.065 9.06533C34.2056 8.92589 34.3172 8.75999 34.3933 8.5772C34.4695 8.39441 34.5087 8.19835 34.5087 8.00033C34.5087 7.80231 34.4695 7.60626 34.3933 7.42347C34.3172 7.24068 34.2056 7.07478 34.065 6.93533Z"
                            />
                        </svg>

                        <p
                            class="text-sm text-center text-gray-400 dark:text-gray-400"
                        >
                            {{ props.placeholder || "Pilih gambar" }}
                        </p>
                    </div>
                </div>
            </div>

            <input
                :id="props.id"
                type="file"
                :onchange="loadFile"
                class="hidden"
            />
        </label>
        <InputError class="mt-1" :message="props.error" />
    </div>
</template>

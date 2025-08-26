<script setup lang="ts">
import Dropdown from "@/Components/Dropdown.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import TextInput from "@/Components/TextInput.vue";
import { ref, watch } from "vue";

interface DropdownOption {
    label: string;
    value: string | number;
    icon?: string | null;
}

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: null,
    },
    modelValue: {
        type: Object as () => DropdownOption,
        default: null,
    },
    options: {
        type: Array as () => DropdownOption[],
        required: true,
    },
    error: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: "text", // "text" or "textarea"
    },
    rows: {
        type: Number,
        default: 1,
    },
    preventNewLine: {
        type: Boolean,
        default: true,
    },
    bgClass: {
        type: String,
        default: null,
    },
    autoResize: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue", "clear", "search"]);

const isDropdownOpen = ref(false);
const search = ref("");

watch(
    () => search.value,
    (newValue) => {
        emit("search", newValue);
    }
);

const dropdown = ref(null);

function onFocusout() {
    setTimeout(() => {
        if (dropdown.value != null) {
            dropdown.value.open = false;
        }
    }, 100);
}
</script>

<template>
    <Dropdown
        ref="dropdown"
        align="left"
        width="full"
        required
        class="w-full"
        @onOpen="isDropdownOpen = true"
        @onClose="isDropdownOpen = false"
    >
        <template #trigger>
            <TextInput
                v-if="props.type === 'text'"
                :modelValue="
                    props.modelValue && !isDropdownOpen
                        ? props.modelValue.label
                        : search
                "
                @update:modelValue="
                    props.modelValue && !isDropdownOpen
                        ? null
                        : (search = $event)
                "
                class="w-full"
                :bgClass="props.bgClass"
                :placeholder="props.placeholder"
                :error="props.error"
                @focus="dropdown.open = true"
                @focusout="onFocusout"
            >
                <template #suffix>
                    <button
                        v-if="
                            props.modelValue &&
                            !isDropdownOpen &&
                            !props.required
                        "
                        type="button"
                        class="absolute p-[7px] text-gray-400 bg-white rounded-full right-1 hover:bg-gray-100 transition-all duration-300 ease-in-out"
                        @click="
                            isDropdownOpen = false;
                            search = '';
                            emit('clear');
                        "
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            class="size-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                    <button v-else type="button" class="absolute p-2 right-1">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            class="size-4 fill-gray-400"
                        >
                            <path
                                d="M18.6054 7.3997C18.4811 7.273 18.3335 7.17248 18.1709 7.10389C18.0084 7.0353 17.8342 7 17.6583 7C17.4823 7 17.3081 7.0353 17.1456 7.10389C16.9831 7.17248 16.8355 7.273 16.7112 7.3997L11.4988 12.7028L6.28648 7.3997C6.03529 7.14415 5.69462 7.00058 5.33939 7.00058C4.98416 7.00058 4.64348 7.14415 4.3923 7.3997C4.14111 7.65526 4 8.00186 4 8.36327C4 8.72468 4.14111 9.07129 4.3923 9.32684L10.5585 15.6003C10.6827 15.727 10.8304 15.8275 10.9929 15.8961C11.1554 15.9647 11.3296 16 11.5055 16C11.6815 16 11.8557 15.9647 12.0182 15.8961C12.1807 15.8275 12.3284 15.727 12.4526 15.6003L18.6188 9.32684C19.1293 8.80747 19.1293 7.93274 18.6054 7.3997Z"
                            />
                        </svg>
                    </button>
                </template>
            </TextInput>
            <TextAreaInput
                v-else-if="props.type === 'textarea'"
                :modelValue="
                    props.modelValue && !isDropdownOpen
                        ? props.modelValue.label
                        : search
                "
                @update:modelValue="
                    props.modelValue && !isDropdownOpen
                        ? null
                        : (search = $event)
                "
                class="w-full"
                :placeholder="props.placeholder"
                :rows="props.rows"
                :preventNewLine="props.preventNewLine"
                :error="props.error"
                @focus="dropdown.open = true"
                @focusout="onFocusout"
            >
                <template #suffix>
                    <button
                        v-if="props.modelValue && !isDropdownOpen"
                        type="button"
                        class="absolute p-[7px] text-gray-400 bg-white rounded-full right-1 hover:bg-gray-100 transition-all duration-300 ease-in-out top-1"
                        @click="
                            isDropdownOpen = false;
                            search = '';
                            emit('clear');
                        "
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            class="size-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                    <button
                        v-else
                        type="button"
                        class="absolute p-2 right-1 top-1"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            class="size-4 fill-gray-400"
                        >
                            <path
                                d="M18.6054 7.3997C18.4811 7.273 18.3335 7.17248 18.1709 7.10389C18.0084 7.0353 17.8342 7 17.6583 7C17.4823 7 17.3081 7.0353 17.1456 7.10389C16.9831 7.17248 16.8355 7.273 16.7112 7.3997L11.4988 12.7028L6.28648 7.3997C6.03529 7.14415 5.69462 7.00058 5.33939 7.00058C4.98416 7.00058 4.64348 7.14415 4.3923 7.3997C4.14111 7.65526 4 8.00186 4 8.36327C4 8.72468 4.14111 9.07129 4.3923 9.32684L10.5585 15.6003C10.6827 15.727 10.8304 15.8275 10.9929 15.8961C11.1554 15.9647 11.3296 16 11.5055 16C11.6815 16 11.8557 15.9647 12.0182 15.8961C12.1807 15.8275 12.3284 15.727 12.4526 15.6003L18.6188 9.32684C19.1293 8.80747 19.1293 7.93274 18.6054 7.3997Z"
                            />
                        </svg>
                    </button>
                </template>
            </TextAreaInput>
        </template>
        <template #content>
            <div>
                <div v-if="$slots.optionHeader" class="px-4 py-2">
                    <slot name="optionHeader" />
                </div>
                <ul class="overflow-y-auto max-h-60">
                    <li
                        v-for="option in props.options"
                        :key="option.value"
                        @click="
                            emit('update:modelValue', option);
                            isDropdownOpen = false;
                            search = '';
                        "
                        class="flex items-center gap-2 px-4 py-2 text-sm cursor-pointer hover:bg-gray-100"
                    >
                        <span v-if="option.icon" class="flex-shrink-0">
                            <img
                                :src="option.icon"
                                :alt="option.label"
                                class="w-5 h-5 rounded-full"
                            />
                        </span>
                        <span>{{ option.label }}</span>
                    </li>
                </ul>
            </div>
        </template>
    </Dropdown>
</template>

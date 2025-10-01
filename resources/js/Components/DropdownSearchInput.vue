<script setup lang="ts">
import Dropdown from "@/Components/Dropdown.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import TextInput from "@/Components/TextInput.vue";
import { computed, ref, watch } from "vue";

interface DropdownOption {
    label: string;
    value: string | number;
    icon?: string | null;
    hexCode?: string | null;
    disabled?: boolean | null;
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
    disabled: {
        type: Boolean,
        default: false,
    },
    searchable: {
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

const searchInput = ref(null);

const filteredOptions = computed(() => {
    if (props.searchable && search.value.length > 0) {
        return props.options.filter((option) =>
            option.label
                .toLowerCase()
                .includes(search.value.toLowerCase().trim())
        );
    } else {
        return props.options;
    }
});
</script>

<template>
    <Dropdown
        ref="dropdown"
        align="left"
        width="full"
        required
        class="w-full"
        :autoClose="false"
        @onOpen="
            isDropdownOpen = true;
            $nextTick(() => searchInput?.focus());
        "
        @onClose="isDropdownOpen = false"
    >
        <template #trigger>
            <TextInput
                v-if="props.type === 'text'"
                :id="`text-input-${props.id}`"
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
                :textClass="props.modelValue?.hexCode ? 'pl-10' : ''"
                :placeholder="props.placeholder"
                :error="props.error"
                :disabled="props.disabled"
                @focus="dropdown.open = true"
                @focusout="onFocusout"
            >
                <template v-if="props.modelValue?.hexCode" #prefix>
                    <span
                        class="absolute w-5 h-5 border rounded-full left-3"
                        :style="`background-color: ${props.modelValue.hexCode}`"
                    ></span>
                </template>

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
                    <button
                        v-else
                        type="button"
                        class="absolute p-2 right-1"
                        :disabled="props.disabled"
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
            </TextInput>
            <template v-else-if="props.type === 'textarea'">
                <div
                    class="relative w-full px-4 py-2 pr-10 text-sm border border-gray-200 rounded-lg shadow-sm cursor-pointer focus:border-primary-light focus:ring-primary-light overflow-ellipsis text-start"
                    :class="[
                        {
                            'border-red-500 focus:border-red-500 focus:ring-red-500':
                                props.error,
                        },
                        {
                            'cursor-auto': props.disabled,
                        },
                    ]"
                >
                    <template v-if="props.modelValue?.label">
                        {{ props.modelValue?.label }}
                        <button
                            type="button"
                            class="absolute top-0.5 right-1 p-[7px] text-gray-400 bg-white rounded-full hover:bg-gray-100 transition-all duration-300 ease-in-out"
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
                    </template>
                    <template v-else>
                        <p class="text-gray-400">{{ props.placeholder }}</p>
                        <button
                            type="button"
                            class="absolute p-2 right-1 top-0.5"
                            :disabled="props.disabled"
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
                </div>
            </template>
        </template>
        <template #content>
            <div>
                <div v-if="$slots.optionHeader" class="px-4 py-2">
                    <slot name="optionHeader" />
                </div>
                <div v-if="props.type === 'textarea'" class="px-4 py-2">
                    <TextInput
                        ref="searchInput"
                        :id="`textarea-input-search-${props.id}`"
                        :modelValue="search"
                        @update:modelValue="search = $event"
                        class="w-full opacity-100"
                        :bgClass="props.bgClass"
                        :textClass="props.modelValue?.hexCode ? 'pl-10' : ''"
                        :placeholder="props.placeholder"
                        :error="props.error"
                        :disabled="props.disabled"
                    >
                        <template #prefix v-if="props.modelValue?.hexCode">
                            <span
                                class="absolute w-5 h-5 border rounded-full left-3"
                                :style="`background-color: ${props.modelValue.hexCode}`"
                            ></span>
                        </template>

                        <template #suffix>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                class="absolute -translate-y-1/2 fill-gray-400 right-3 top-1/2 size-5"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M11 17C11.7879 17 12.5681 16.8448 13.2961 16.5433C14.0241 16.2417 14.6855 15.7998 15.2426 15.2426C15.7998 14.6855 16.2417 14.0241 16.5433 13.2961C16.8448 12.5681 17 11.7879 17 11C17 10.2121 16.8448 9.43185 16.5433 8.7039C16.2417 7.97595 15.7998 7.31451 15.2426 6.75736C14.6855 6.20021 14.0241 5.75825 13.2961 5.45672C12.5681 5.15519 11.7879 5 11 5C9.4087 5 7.88258 5.63214 6.75736 6.75736C5.63214 7.88258 5 9.4087 5 11C5 12.5913 5.63214 14.1174 6.75736 15.2426C7.88258 16.3679 9.4087 17 11 17ZM11 19C13.1217 19 15.1566 18.1571 16.6569 16.6569C18.1571 15.1566 19 13.1217 19 11C19 8.87827 18.1571 6.84344 16.6569 5.34315C15.1566 3.84285 13.1217 3 11 3C8.87827 3 6.84344 3.84285 5.34315 5.34315C3.84285 6.84344 3 8.87827 3 11C3 13.1217 3.84285 15.1566 5.34315 16.6569C6.84344 18.1571 8.87827 19 11 19Z"
                                />
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M15.3201 15.2903C15.5082 15.1035 15.7629 14.9991 16.0281 15C16.2933 15.0009 16.5472 15.1072 16.7341 15.2953L20.7091 19.2953C20.8908 19.4844 20.9909 19.7373 20.9879 19.9995C20.9849 20.2618 20.879 20.5123 20.6931 20.6972C20.5071 20.8822 20.256 20.9866 19.9937 20.9881C19.7315 20.9896 19.4791 20.8881 19.2911 20.7053L15.3161 16.7053C15.1291 16.5172 15.0245 16.2626 15.0253 15.9975C15.026 15.7323 15.1321 15.4783 15.3201 15.2913V15.2903Z"
                                />
                            </svg>
                        </template>
                    </TextInput>
                </div>
                <ul class="overflow-y-auto max-h-60">
                    <li
                        v-for="option in filteredOptions"
                        :key="option.value"
                        class="flex items-center gap-2 px-4 py-2 text-sm cursor-pointer hover:bg-gray-100"
                        :class="
                            option.disabled
                                ? 'opacity-50 !cursor-default hover:bg-transparent'
                                : ''
                        "
                        @click="
                            if (!option.disabled) {
                                emit('update:modelValue', option);
                                isDropdownOpen = false;
                                dropdown.open = false;
                                search = '';
                            }
                        "
                    >
                        <span v-if="option.icon" class="flex-shrink-0">
                            <img
                                :src="option.icon"
                                :alt="option.label"
                                class="w-5 h-5 rounded-full"
                            />
                        </span>
                        <span
                            v-else-if="option.hexCode"
                            class="w-5 h-5 border rounded-full"
                            :style="`background-color: ${option.hexCode}`"
                        ></span>
                        <span>{{ option.label }}</span>
                    </li>
                </ul>
            </div>
        </template>
    </Dropdown>
</template>

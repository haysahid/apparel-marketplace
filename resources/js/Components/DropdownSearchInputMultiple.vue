<script setup lang="ts">
import Dropdown from "@/Components/Dropdown.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import TextInput from "@/Components/TextInput.vue";
import { ref, watch, computed } from "vue";

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
        type: Array as () => DropdownOption[],
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
});

const emit = defineEmits(["update:modelValue", "clear", "search"]);

const isDropdownOpen = ref(false);
const search = ref("");

const filteredOptions = computed(() => {
    return props.options.filter(
        (option) =>
            !props.modelValue.some((item) => item.value === option.value)
    );
});

watch(
    () => search.value,
    (newValue) => {
        emit("search", newValue);
    }
);

const dropdown = ref(null);

function onFocusout() {
    setTimeout(() => {
        dropdown.value.open = false;
    }, 100);
}
</script>

<template>
    <div>
        <Dropdown
            ref="dropdown"
            align="left"
            width="full"
            required
            class="w-full"
            :error="props.error"
            @onOpen="isDropdownOpen = true"
            @onClose="isDropdownOpen = false"
        >
            <template #trigger>
                <TextInput
                    v-if="props.type === 'text'"
                    :modelValue="search"
                    @update:modelValue="
                        props.modelValue && !isDropdownOpen
                            ? null
                            : (search = $event)
                    "
                    class="w-full"
                    :textClass="
                        props.autoResize ? 'text-sm sm:text-base' : null
                    "
                    :bgClass="props.bgClass"
                    :placeholder="props.placeholder"
                    :error="props.error"
                    @focus="dropdown.open = true"
                    @focusout="onFocusout"
                >
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
            </template>
            <template #content>
                <div>
                    <div v-if="$slots.optionHeader" class="px-4 py-2">
                        <slot name="optionHeader" />
                    </div>
                    <ul class="overflow-y-auto max-h-60">
                        <li
                            v-for="option in filteredOptions"
                            :key="option.value"
                            @click="
                                emit('update:modelValue', [
                                    ...props.modelValue,
                                    option,
                                ]);
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

        <div
            v-if="props.modelValue && props.modelValue.length > 0"
            class="flex flex-wrap gap-2 mt-2.5"
        >
            <div v-for="(option, index) in props.modelValue" :key="index">
                <div
                    class="flex items-center gap-2 py-2 pl-4 pr-2 text-sm bg-gray-100 rounded-lg"
                >
                    <p>{{ option.label }}</p>
                    <button
                        type="button"
                        class="text-gray-400 hover:text-gray-500"
                        @click="
                            emit(
                                'update:modelValue',
                                props.modelValue.filter(
                                    (item) => item.value !== option.value
                                )
                            )
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
                </div>
            </div>
        </div>
    </div>
</template>

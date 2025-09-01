<script setup>
import { onMounted, ref, onUpdated } from "vue";
import { useSlots } from "vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    id: {
        type: String,
        default: null,
    },
    name: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: "text",
    },
    placeholder: {
        type: String,
        default: "Masukkan teks...",
    },
    autofocus: {
        type: Boolean,
        default: false,
    },
    autocomplete: {
        type: String,
        default: null,
    },
    required: {
        type: Boolean,
        default: false,
    },
    rows: {
        type: Number,
        default: 4,
    },
    height: {
        type: String,
        default: "max-h-[300px]",
    },
    errorClass: {
        type: String,
        default: "",
    },
    preventNewLine: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
    modelValue: String,
});

const emit = defineEmits(["update:modelValue", "focusout", "focus"]);

const input = ref(null);

const slots = useSlots();
const hasPrefix = ref(!!slots.prefix);
const hasSuffix = ref(!!slots.suffix);

function updateValue(value) {
    if (props.preventNewLine) {
        value = value.replace(/\n/g, " ");
    }

    emit("update:modelValue", value);
}

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }

    input.value.parentNode.dataset.clonedVal = props.modelValue;
});

onUpdated(() => {
    hasPrefix.value = !!slots.prefix;
    hasSuffix.value = !!slots.suffix;
    input.value.parentNode.dataset.clonedVal = props.modelValue;
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div>
        <label
            :for="id"
            class="relative grid after:px-4 after:py-2 [&>textarea]:text-inherit after:text-inherit [&>textarea]:resize-none [&>textarea]:overflow-auto [&>textarea]:[grid-area:1/1/2/2] after:[grid-area:1/1/2/2] after:whitespace-pre-wrap after:invisible after:content-[attr(data-cloned-val)_'_'] after:border max-h-[300px] text-sm"
            :class="[
                props.height,
                {
                    'after:pl-11': hasPrefix,
                    'after:pr-11': hasSuffix,
                },
            ]"
        >
            <slot name="prefix"></slot>
            <textarea
                ref="input"
                :id="props.id"
                :name="props.name"
                :placeholder="props.placeholder"
                :type="props.type"
                :autofocus="props.autofocus ? true : false"
                :autocomplete="props.autocomplete"
                :rows="props.rows"
                class="w-full px-4 py-2 border-gray-200 shadow-sm rounded-lg focus:border-primary-light focus:ring-primary-light max-h-[300px] text-sm"
                :class="[
                    {
                        'pl-11': hasPrefix,
                        'pr-11': hasSuffix,
                        'border-red-500 focus:border-red-500 focus:ring-red-500':
                            props.error,
                    },
                    props.height,
                ]"
                :value="props.modelValue"
                @input="updateValue($event.target.value)"
                @focus="emit('focus')"
                @focusout="emit('focusout')"
            />
            <slot name="suffix"></slot>
        </label>
        <InputError
            :message="props.error"
            class="px-4 mt-1"
            :class="[props.errorClass]"
        />
    </div>
</template>

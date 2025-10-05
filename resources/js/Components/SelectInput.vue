<script setup lang="ts">
interface SelectOption {
    label: string;
    value: string | number;
    disabled?: boolean | null;
}

const props = defineProps({
    options: {
        type: Array as () => SelectOption[],
        required: true,
    },
    modelValue: {
        type: [String, Number],
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: "-- Pilih --",
    },
});

const emit = defineEmits({
    "update:modelValue": (value: string | number) => true,
});
</script>

<template>
    <select
        class="px-3 py-1 text-sm text-gray-600 transition-all duration-300 ease-in-out border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:ring-1 focus:ring-primary-light focus:border-primary-light hover:border-primary-light hover:ring-1 hover:ring-primary-light disabled:hover:border-gray-300 disabled:ring-0 disabled:cursor-default"
        :disabled="props.disabled"
        @change="(e) => emit('update:modelValue', (e.target as HTMLSelectElement).value)"
    >
        <option value="" disabled :selected="!props.modelValue" hidden>
            {{ props.placeholder }}
        </option>

        <option
            v-for="option in props.options"
            :key="option.value"
            :value="option.value"
            :selected="option.value === props.modelValue"
            :disabled="option.disabled"
        >
            {{ option.label }}
        </option>
    </select>
</template>

<script setup lang="ts">
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { ref, computed } from "vue";

const props = defineProps({
    unit: {
        type: String,
        default: null,
    },
    max: {
        type: Number,
        default: null,
    },
    modelValue: {
        type: Number,
        default: 1,
    },
    label: {
        type: String,
        default: "Jumlah",
    },
    showAvailability: {
        type: Boolean,
        default: true,
    },
    bgClass: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

function updateValue(value: number) {
    emit("update:modelValue", Math.max(1, value));
}
</script>

<template>
    <div class="flex items-center gap-4">
        <InputLabel
            v-if="label"
            for="quantity"
            :value="label"
            class="!text-gray-500 min-w-[80px] h-min"
        />
        <div class="flex items-center gap-4">
            <TextInput
                id="quantity"
                :modelValue="props.modelValue"
                type="number"
                min="1"
                :hide-arrows="true"
                class="w-[120px] grow-0"
                :bgClass="
                    'rounded-lg bg-white px-3.5 border-gray-400 ' + bgClass
                "
                textClass="text-center"
                placeholder="0"
                errorClass="px-0"
                :error="
                    !props.modelValue || (props.max && props.modelValue <= 0)
                        ? 'Jumlah tidak valid'
                        : props.max && props.modelValue > props.max
                        ? 'Stok tidak cukup'
                        : ''
                "
                @keydown="
                    (event) => {
                        if (/^[eE\-\+]$/.test(event.key)) {
                            event.preventDefault();
                        }
                    }
                "
                @paste="
                    (event) => {
                        const value = event.clipboardData.getData('text');
                        const parsedValue = parseInt(value, 10);

                        if (value === '' || isNaN(parsedValue)) {
                            updateValue(1);
                        } else if (parsedValue < 1) {
                            updateValue(parsedValue * -1);
                        } else {
                            updateValue(parsedValue);
                        }
                        event.preventDefault();
                    }
                "
                @input="updateValue($event.target.valueAsNumber)"
            >
                <template #prefix>
                    <button
                        class="absolute p-1 text-gray-600 left-2 hover:text-gray-800 disabled:opacity-30"
                        @click="updateValue(props.modelValue - 1)"
                        :disabled="props.modelValue <= 1"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="17"
                            viewBox="0 0 16 17"
                            fill="none"
                        >
                            <path
                                d="M12 8.69482H4"
                                stroke="black"
                                stroke-width="1.2381"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </button>
                </template>
                <template #suffix>
                    <button
                        class="absolute p-1 text-gray-600 right-2 size-6 hover:text-gray-800 disabled:opacity-30"
                        @click="updateValue(props.modelValue + 1)"
                        :disabled="props.max && props.modelValue >= props.max"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="17"
                            viewBox="0 0 16 17"
                            fill="none"
                        >
                            <path
                                d="M13.3334 8.69491H2.66669M8.00002 3.36157V14.0282V3.36157Z"
                                stroke="black"
                                stroke-width="1.2381"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </button>
                </template>
            </TextInput>

            <p
                v-if="props.max && props.showAvailability"
                class="text-sm text-gray-600"
            >
                Tersedia {{ props.max }}
                {{ props.unit }}
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps({
    media: {
        type: Object as () => MediaEntity | TemporaryMediaEntity,
        required: true,
    },
    showName: {
        type: Boolean,
        default: true,
    },
    showSize: {
        type: Boolean,
        default: true,
    },
    isSelected: {
        type: Boolean,
        default: false,
    },
    showCheckbox: {
        type: Boolean,
        default: true,
    },
    showRemoveButton: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits<{
    (e: "update:isSelected", value: boolean): void;
    (e: "remove", id: number | string): void;
}>();

const proxyChecked = computed({
    get() {
        return props.isSelected;
    },

    set(val) {
        emit("update:isSelected", val);
    },
});
</script>

<template>
    <button
        type="button"
        class="relative overflow-hidden transition-all ease-in-out border rounded-lg cursor-pointer group"
        :class="{
            'border-blue-500 ring-1 ring-blue-500': props.isSelected,
        }"
        @click="proxyChecked = !proxyChecked"
    >
        <img
            :src="props.media.original_url"
            alt="media"
            class="object-cover w-full h-32 transition-transform duration-200 group-hover:scale-105"
        />
        <div v-if="props.showName || props.showSize" class="p-2">
            <p v-if="props.showName" class="text-sm font-medium">
                {{ props.media.file_name }}
            </p>
            <p v-if="props.showSize" class="text-xs text-gray-600">
                {{ (props.media.size / 1024).toFixed(2) }} KB
            </p>
        </div>

        <!-- Checkbox -->
        <div v-if="props.showCheckbox" class="absolute top-1 left-2">
            <input
                type="checkbox"
                :value="props.media.id"
                v-model="proxyChecked"
                class="w-4 h-4 text-blue-600 transition-all ease-in-out border-gray-300 rounded focus:ring-blue-500"
            />
        </div>

        <!-- Remove Button -->
        <button
            v-if="props.showRemoveButton"
            type="button"
            class="absolute p-0.5 text-white transition-all duration-300 ease-in-out rounded bg-black/10 top-1 right-1 hover:bg-red-500"
            @click.stop="emit('remove', props.media.id)"
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
    </button>
</template>

<script setup>
import InputLabel from "@/Components/InputLabel.vue";

const props = defineProps({
    for: {
        type: String,
        default: null,
    },
    label: {
        type: String,
        required: true,
    },
    required: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="flex flex-col w-full gap-y-2">
        <div class="flex items-center justify-between gap-x-2">
            <div class="flex items-center gap-x-1">
                <InputLabel
                    :for="props.for"
                    :value="props.label"
                    :required="props.required"
                />
                <slot name="suffix" />
            </div>
            <slot name="trailing" />
        </div>
        <slot />
    </div>
</template>

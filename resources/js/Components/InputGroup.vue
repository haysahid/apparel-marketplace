<script setup>
import InputLabel from "@/Components/InputLabel.vue";

const props = defineProps({
    id: {
        type: String,
        default: null,
    },
    label: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <div class="flex flex-col w-full gap-y-2">
        <InputLabel
            :for="props.id"
            :value="props.label"
        />
        <slot />
    </div>
</template>

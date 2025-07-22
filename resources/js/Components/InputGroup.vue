<script setup>
import InputLabel from "@/Components/InputLabel.vue";

const props = defineProps({
    id: {
        type: String,
        required: true,
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
            class="text-lg font-bold"
        />
        <slot />
    </div>
</template>

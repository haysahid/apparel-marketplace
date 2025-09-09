<script setup>
const props = defineProps({
    isEmpty: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="overflow-x-auto rounded-md outline outline-1 outline-gray-200">
        <table class="table-default">
            <thead v-if="$slots.thead" class="">
                <slot name="thead" />
            </thead>
            <tbody v-if="!isEmpty">
                <slot name="tbody" />
            </tbody>
        </table>

        <!-- Empty State -->
        <div v-if="isEmpty" class="flex items-center justify-center h-[40vh]">
            <div class="flex flex-col items-center justify-center gap-2">
                <p class="text-sm text-center text-gray-500">
                    Data tidak ditemukan.
                </p>
                <slot name="emptyAction" />
            </div>
        </div>
    </div>
</template>

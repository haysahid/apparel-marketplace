<script setup>
const props = defineProps({
    isEmpty: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div
        class="border border-gray-200 rounded-md lg:block overflow-x-auto h-[68vh]"
    >
        <table class="table-default">
            <thead v-if="$slots.thead" class="sticky top-0">
                <slot name="thead" />
            </thead>
            <tbody v-if="!isEmpty">
                <slot name="tbody" />
            </tbody>
        </table>

        <!-- Empty State -->
        <div v-if="isEmpty" class="flex items-center justify-center h-[90%]">
            <div class="flex flex-col items-center justify-center gap-2">
                <p class="text-sm text-center text-gray-500">
                    Data tidak ditemukan.
                </p>
                <slot name="emptyAction" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import AdminItemAction from "@/Components/AdminItemAction.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

const props = defineProps({
    voucher: {
        type: Object as () => VoucherEntity,
        required: true,
    },
});

const emit = defineEmits(["edit", "delete"]);
</script>

<template>
    <div
        class="flex items-start justify-between gap-2.5 p-2.5 sm:gap-3 sm:p-4 transition-all duration-300 ease-in-out border border-gray-200 rounded-lg hover:border-primary-light hover:ring-1 hover:ring-primary-light"
    >
        <div class="flex flex-col items-start w-full gap-1">
            <p class="text-sm font-medium text-gray-900">
                {{ props.voucher.name }}
            </p>
            <p class="text-xs text-gray-500 line-clamp-2 overflow-ellipsis">
                {{ props.voucher.code }}
            </p>
            <p class="text-xs text-gray-500 line-clamp-2 overflow-ellipsis">
                {{ props.voucher.description }}
            </p>
        </div>

        <AdminItemAction :showMore="true">
            <template #moreContent>
                <div class="divide-y divide-gray-200">
                    <DropdownLink as="button" @click="emit('edit')">
                        Ubah
                    </DropdownLink>
                    <DropdownLink as="button" @click="emit('delete')">
                        Hapus
                    </DropdownLink>
                </div>
            </template>
        </AdminItemAction>
    </div>
</template>

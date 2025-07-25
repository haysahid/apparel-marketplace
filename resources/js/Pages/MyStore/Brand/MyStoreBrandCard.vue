<script setup lang="ts">
import AdminItemAction from "@/Components/AdminItemAction.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

const props = defineProps({
    brand: {
        type: Object as () => BrandEntity,
        required: true,
    },
});

const emit = defineEmits(["edit", "delete"]);
</script>

<template>
    <div
        class="flex items-center justify-between gap-2.5 p-2.5 sm:gap-3 sm:p-4 transition-all duration-300 ease-in-out border border-gray-200 rounded-lg hover:border-primary-light hover:ring-1 hover:ring-primary-light"
    >
        <img
            v-if="props.brand.logo"
            :src="props.brand.logo"
            alt="Brand Logo"
            class="object-contain w-[80px] sm:w-[100px] rounded aspect-[3/2]"
        />
        <div
            v-else
            class="flex items-center justify-center min-w-[80px] sm:min-w-[100px] bg-gray-100 rounded aspect-[3/2]"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                class="size-6 fill-gray-400"
            >
                <path
                    d="M5 21C4.45 21 3.97933 20.8043 3.588 20.413C3.19667 20.0217 3.00067 19.5507 3 19V5C3 4.45 3.196 3.97933 3.588 3.588C3.98 3.19667 4.45067 3.00067 5 3H19C19.55 3 20.021 3.196 20.413 3.588C20.805 3.98 21.0007 4.45067 21 5V19C21 19.55 20.8043 20.021 20.413 20.413C20.0217 20.805 19.5507 21.0007 19 21H5ZM6 17H18L14.25 12L11.25 16L9 13L6 17Z"
                />
            </svg>
        </div>
        <div class="flex flex-col items-start w-full gap-1">
            <p class="text-sm font-medium text-gray-900">
                {{ props.brand.name }}
            </p>
            <p class="text-xs text-gray-500 line-clamp-2 overflow-ellipsis">
                {{ props.brand.description }}
            </p>
        </div>

        <AdminItemAction :showMore="true" class="self-start">
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

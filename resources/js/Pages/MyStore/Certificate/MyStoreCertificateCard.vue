<script setup lang="ts">
import AdminItemAction from "@/Components/AdminItemAction.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { getImageUrl } from "@/plugins/helpers";

const props = defineProps({
    certificate: {
        type: Object as () => StoreCertificateEntity,
        required: true,
    },
});

const emit = defineEmits(["edit", "delete"]);
</script>

<template>
    <div
        class="flex items-start justify-between gap-2.5 p-2.5 sm:gap-3 sm:p-4 transition-all duration-300 ease-in-out border border-gray-200 rounded-lg hover:border-primary-light hover:ring-1 hover:ring-primary-light"
    >
        <img
            :src="getImageUrl(props.certificate.image)"
            alt="Sertifikat"
            class="object-cover w-[80px] sm:w-[100px] rounded aspect-[3/2] border border-gray-200"
        />
        <div class="flex flex-col items-start w-full gap-1">
            <p class="text-sm font-medium text-gray-900">
                {{ props.certificate.name }}
            </p>
            <p class="text-xs text-gray-500 line-clamp-2 overflow-ellipsis">
                {{ props.certificate.description }}
            </p>
        </div>

        <AdminItemAction>
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

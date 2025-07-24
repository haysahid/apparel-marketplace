<script setup lang="ts">
import DialogModal from "@/Components/DialogModal.vue";
import StoreItem from "@/Components/StoreItem.vue";

const props = defineProps({
    title: {
        type: String,
        default: "Pilih Toko",
    },
    stores: {
        type: Array as () => StoreEntity[],
        default: () => [],
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close", "select"]);
</script>

<template>
    <DialogModal
        :show="props.show"
        :showCloseButton="true"
        @close="emit('close')"
        maxWidth="sm"
    >
        <template #title>{{ props.title }}</template>
        <template #content>
            <div v-if="props.stores.length > 0" class="w-full">
                <p class="mb-2 text-sm text-gray-700">
                    Pilih salah satu toko di bawah ini.
                </p>
                <div class="flex flex-col gap-2 p-1.5 w-full">
                    <StoreItem
                        v-for="store in props.stores"
                        :key="store.id"
                        :name="store.name"
                        :description="store.description"
                        :icon="store.logo ? `/storage/${store.logo}` : null"
                        @click="emit('select', store)"
                    />
                </div>
            </div>
            <div v-else class="p-1.5 text-sm text-center text-gray-500">
                Anda belum memiliki toko.
            </div>
        </template>
    </DialogModal>
</template>

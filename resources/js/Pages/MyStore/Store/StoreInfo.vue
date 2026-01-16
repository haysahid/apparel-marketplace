<script setup lang="ts">
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import StoreForm from "@/Pages/Admin/Store/StoreForm.vue";

const props = defineProps({
    store: {
        type: Object,
        required: null,
    },
});
</script>

<template>
    <MyStoreLayout title="Informasi Toko" :showTitle="true">
        <DefaultCard>
            <StoreForm :store="props.store" />
        </DefaultCard>
    </MyStoreLayout>
</template>

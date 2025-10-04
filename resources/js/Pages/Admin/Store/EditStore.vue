<script setup>
import StoreForm from "./StoreForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AdminLayout
        title="Ubah Toko"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Toko', url: '/admin/store', active: false },
            { text: 'Ubah Toko', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <StoreForm :store="store" />
            </DefaultCard>
        </div>
    </AdminLayout>
</template>

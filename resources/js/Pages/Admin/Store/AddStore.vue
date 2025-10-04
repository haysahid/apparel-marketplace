<script setup>
import StoreForm from "./StoreForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
</script>

<template>
    <AdminLayout
        title="Tambah Toko"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Toko', url: '/admin/store', active: false },
            { text: 'Tambah Toko', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <StoreForm />
            </DefaultCard>
        </div>
    </AdminLayout>
</template>

<script setup>
import DefaultCard from "@/Components/DefaultCard.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import UserForm from "./UserForm.vue";
</script>

<template>
    <AdminLayout
        title="Tambah Pengguna"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Pengguna', url: '/admin/user', active: false },
            { text: 'Tambah Pengguna', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <UserForm />
            </DefaultCard>
        </div>
    </AdminLayout>
</template>

<script setup>
import DefaultCard from "@/Components/DefaultCard.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import UserForm from "./UserForm.vue";

const props = defineProps({
    user: Object,
});
</script>

<template>
    <AdminLayout
        title="Ubah Pengguna"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Pengguna', url: '/admin/user', active: false },
            { text: 'Ubah Pengguna', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <UserForm :user="user" />
            </DefaultCard>
        </div>
    </AdminLayout>
</template>

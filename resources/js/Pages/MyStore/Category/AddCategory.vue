<script setup>
import CategoryForm from "./CategoryForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
</script>

<template>
    <MyStoreLayout
        title="Tambah Kategori"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Kategori', url: '/my-store/category', active: false },
            { text: 'Tambah Kategori', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <CategoryForm />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

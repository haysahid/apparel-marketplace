<script setup>
import ProductForm from "./ProductForm.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
</script>

<template>
    <MyStoreLayout
        title="Tambah Produk"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Produk', url: '/my-store/product', active: false },
            { text: 'Tambah Produk', active: true },
        ]"
    >
        <ProductForm />
    </MyStoreLayout>
</template>

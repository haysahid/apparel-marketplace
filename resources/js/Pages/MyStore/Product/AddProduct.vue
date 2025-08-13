<script setup>
import ProductForm from "./ProductForm.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
</script>

<template>
    <MyStoreLayout title="Tambah Produk" :showTitle="true">
        <ProductForm />
    </MyStoreLayout>
</template>

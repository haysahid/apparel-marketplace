<script setup>
import ProductForm from "./ProductForm.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <MyStoreLayout title="Ubah Produk" :showTitle="true">
        <ProductForm :product="props.product" />
    </MyStoreLayout>
</template>

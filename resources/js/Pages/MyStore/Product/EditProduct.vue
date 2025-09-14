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
    <MyStoreLayout
        title="Ubah Produk"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Produk', url: '/my-store/product', active: false },
            { text: 'Ubah Produk', active: true },
        ]"
    >
        <ProductForm :product="props.product" />
    </MyStoreLayout>
</template>

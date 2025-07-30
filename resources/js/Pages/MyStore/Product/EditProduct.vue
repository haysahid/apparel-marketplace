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
        <div class="max-w-7xl">
            <DefaultCard>
                <ProductForm :product="props.product" />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

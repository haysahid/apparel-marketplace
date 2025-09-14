<script setup>
import BrandForm from "./BrandForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
</script>

<template>
    <MyStoreLayout
        title="Tambah Brand"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Brand', url: '/my-store/brand', active: false },
            { text: 'Tambah Brand', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <BrandForm />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

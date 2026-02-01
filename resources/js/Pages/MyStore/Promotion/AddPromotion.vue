<script setup>
import PromotionForm from "./PromotionForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
</script>

<template>
    <MyStoreLayout
        title="Tambah Promosi"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Promosi', url: '/my-store/promotion', active: false },
            { text: 'Tambah Promosi', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <PromotionForm />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

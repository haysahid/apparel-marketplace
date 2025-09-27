<script setup>
import PartnerForm from "./PartnerForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
</script>

<template>
    <MyStoreLayout
        title="Tambah Mitra"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Mitra', url: '/my-store/partner', active: false },
            { text: 'Tambah Mitra', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <PartnerForm />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

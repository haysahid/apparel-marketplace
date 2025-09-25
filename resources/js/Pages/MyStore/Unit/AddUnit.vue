<script setup>
import UnitForm from "./UnitForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
</script>

<template>
    <MyStoreLayout
        title="Tambah Satuan"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Satuan', url: '/my-store/unit', active: false },
            { text: 'Tambah Satuan', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <UnitForm />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

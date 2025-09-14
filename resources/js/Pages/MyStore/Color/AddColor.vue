<script setup>
import ColorForm from "./ColorForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
</script>

<template>
    <MyStoreLayout
        title="Tambah Warna"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Warna', url: '/my-store/color', active: false },
            { text: 'Tambah Warna', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <ColorForm />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

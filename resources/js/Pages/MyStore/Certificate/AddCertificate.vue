<script setup>
import CertificateForm from "./CertificateForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
</script>

<template>
    <MyStoreLayout
        title="Tambah Sertifikat"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Sertifikat', url: '/my-store/certificate', active: false },
            { text: 'Tambah Sertifikat', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <CertificateForm />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

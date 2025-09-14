<script setup>
import DefaultCard from "@/Components/DefaultCard.vue";
import CertificateForm from "./CertificateForm.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";

const props = defineProps({
    certificate: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <MyStoreLayout
        title="Ubah Sertifikat"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Sertifikat', url: '/my-store/certificate', active: false },
            { text: 'Ubah Sertifikat', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <CertificateForm :certificate="props.certificate" />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

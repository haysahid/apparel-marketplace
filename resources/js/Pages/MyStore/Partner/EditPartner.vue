<script setup>
import PartnerForm from "./PartnerForm.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

const props = defineProps({
    partner: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <MyStoreLayout
        title="Ubah Mitra"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Mitra', url: '/my-store/partner', active: false },
            { text: 'Ubah Mitra', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <PartnerForm :partner="props.partner" />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

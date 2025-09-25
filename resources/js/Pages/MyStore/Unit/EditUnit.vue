<script setup>
import UnitForm from "./UnitForm.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

const props = defineProps({
    unit: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <MyStoreLayout
        title="Ubah Satuan"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Satuan', url: '/my-store/unit', active: false },
            { text: 'Ubah Satuan', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <UnitForm :unit="props.unit" />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

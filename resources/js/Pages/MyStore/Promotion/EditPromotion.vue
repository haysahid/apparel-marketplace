<script setup>
import PromotionForm from "./PromotionForm.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

const props = defineProps({
    promotion: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <MyStoreLayout
        title="Ubah Promosi"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Promosi', url: '/my-store/promotion', active: false },
            { text: 'Ubah Promosi', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <PromotionForm :promotion="props.promotion" />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

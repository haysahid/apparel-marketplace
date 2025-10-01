<script setup>
import PointRuleForm from "./PointRuleForm.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

const props = defineProps({
    pointRule: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <MyStoreLayout
        title="Ubah Aturan Point"
        :showTitle="true"
        :breadcrumbs="[
            {
                text: 'Aturan Point',
                url: '/my-store/point-rule',
                active: false,
            },
            { text: 'Ubah Aturan Point', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <PointRuleForm :pointRule="props.pointRule" />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

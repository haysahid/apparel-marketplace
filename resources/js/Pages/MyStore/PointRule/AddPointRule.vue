<script setup>
import PointRuleForm from "./PointRuleForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
</script>

<template>
    <MyStoreLayout
        title="Tambah Aturan Poin"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Aturan Poin', url: '/my-store/point-rule', active: false },
            { text: 'Tambah Aturan Poin', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <PointRuleForm />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

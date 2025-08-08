<script setup>
import CategoryForm from "./CategoryForm.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

const props = defineProps({
    category: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <MyStoreLayout title="Ubah Kategori" :showTitle="true">
        <div class="max-w-7xl">
            <DefaultCard>
                <CategoryForm :category="props.category" />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

<script setup>
import CategoryForm from "./SizeForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
</script>

<template>
    <MyStoreLayout title="Tambah Ukuran" :showTitle="true">
        <div class="max-w-7xl">
            <DefaultCard>
                <CategoryForm />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

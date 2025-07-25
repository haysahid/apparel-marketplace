<script setup>
import BrandForm from "./BrandForm.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

const props = defineProps({
    brand: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <MyStoreLayout title="Ubah Brand" :showTitle="true">
        <div class="max-w-7xl">
            <DefaultCard>
                <BrandForm :brand="props.brand" />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

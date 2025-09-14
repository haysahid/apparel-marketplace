<script setup>
import CategoryForm from "./SizeForm.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

const props = defineProps({
    size: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <MyStoreLayout
        title="Ubah Ukuran"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Ukuran', url: '/my-store/size', active: false },
            { text: 'Ubah Ukuran', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <CategoryForm :size="props.size" />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

<script setup>
import ColorForm from "./ColorForm.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

const props = defineProps({
    color: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <MyStoreLayout
        title="Ubah Warna"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Warna', url: '/my-store/color', active: false },
            { text: 'Ubah Warna', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <ColorForm :color="props.color" />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

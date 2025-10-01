<script setup lang="ts">
import MyStoreHeader from "@/Components/MyStoreHeader.vue";
import MyStoreSidebar from "@/Components/MyStoreSidebar.vue";
import BaseLayout from "@/Components/BaseLayout.vue";

const props = defineProps({
    title: String,
    subtitle: {
        type: String,
        default: null,
    },
    showTitle: {
        type: Boolean,
        default: false,
    },
    breadcrumbs: {
        type: Array as () => BreadcrumbItemModel[],
        default: null,
    },
});
</script>

<template>
    <BaseLayout :title="props.title">
        <template #header>
            <MyStoreHeader
                :title="props.title"
                :subtitle="props.subtitle"
                :showTitle="props.showTitle"
                :breadcrumbs="props.breadcrumbs"
            />
        </template>

        <template #sidebar>
            <MyStoreSidebar />
        </template>

        <slot />
    </BaseLayout>
</template>

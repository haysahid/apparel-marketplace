<script setup lang="ts">
import BaseLayout from "@/Components/BaseLayout.vue";
import AdminSidebar from "@/Components/AdminSidebar.vue";
import AdminHeader from "@/Components/AdminHeader.vue";

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
            <AdminHeader
                :title="props.title"
                :subtitle="props.subtitle"
                :showTitle="props.showTitle"
                :breadcrumbs="props.breadcrumbs"
            />
        </template>

        <template #sidebar>
            <AdminSidebar />
        </template>

        <slot />
    </BaseLayout>
</template>

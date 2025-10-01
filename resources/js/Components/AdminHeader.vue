<script setup lang="ts">
import AdminSidebar from "./AdminSidebar.vue";
import BaseHeader from "./BaseHeader.vue";
import UserDropdown from "./UserDropdown.vue";

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
    <BaseHeader>
        <template #leading v-if="showTitle">
            <div>
                <!-- Title -->
                <div class="flex items-center justify-between gap-4 rounded-lg">
                    <div class="flex flex-col items-start max-w-7xl">
                        <h1 class="text-lg font-semibold">
                            {{ props.title }}
                        </h1>
                        <p v-if="subtitle" class="text-xs text-gray-500">
                            {{ props.subtitle }}
                        </p>
                    </div>
                    <slot name="trailing"></slot>
                </div>

                <!-- Breadcrumbs -->
                <p
                    v-if="breadcrumbs && breadcrumbs.length > 0"
                    class="text-xs text-gray-500"
                >
                    <template
                        v-for="(breadcrumb, index) in breadcrumbs"
                        :key="index"
                    >
                        <span v-if="index > 0" class="mx-1">/</span>
                        <a
                            v-if="breadcrumb.url"
                            :href="breadcrumb.url"
                            class="text-gray-500 hover:underline"
                        >
                            {{ breadcrumb.text }}
                        </a>
                        <span v-else class="text-gray-500">
                            {{ breadcrumb.text }}
                        </span>
                    </template>
                </p>
            </div>
        </template>

        <template #trailing>
            <!-- Settings Dropdown -->
            <div class="relative ms-3">
                <UserDropdown />
            </div>
        </template>

        <template #responsiveMenu>
            <AdminSidebar :responsive="true">
                <template #extraMenu>
                    <div class="space-y-0.5 relative">
                        <UserDropdown :responsive="true" />
                    </div>
                </template>
            </AdminSidebar>
        </template>
    </BaseHeader>
</template>

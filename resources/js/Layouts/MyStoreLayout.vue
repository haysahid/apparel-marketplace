<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
import MyStoreHeader from "@/Components/MyStoreHeader.vue";
import MyStoreSidebar from "@/Components/MyStoreSidebar.vue";

defineProps({
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
    <div>
        <Head :title="title" />

        <Banner />

        <!-- Page Content -->
        <div class="flex h-screen bg-secondary-box">
            <!-- Sidebar -->
            <MyStoreSidebar />

            <div
                class="flex-1 w-full transition-all duration-300 ease-in-out md:ml-64 h-[calc(100vh-72px)] sm:min-h-[calc(100vh-72px)] relative mt-[72px] overflow-x-hidden"
            >
                <!-- Header -->
                <MyStoreHeader>
                    <template v-if="showTitle" #leading>
                        <div>
                            <!-- Title -->
                            <div
                                class="flex items-center justify-between gap-4 rounded-lg"
                            >
                                <div
                                    class="flex flex-col items-start max-w-7xl"
                                >
                                    <h1 class="text-lg font-semibold">
                                        {{ title }}
                                    </h1>
                                    <p
                                        v-if="subtitle"
                                        class="text-xs text-gray-500"
                                    >
                                        {{ subtitle }}
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
                </MyStoreHeader>

                <!-- Main Content -->
                <main class="sm:p-6">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

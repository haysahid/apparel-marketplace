<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { getImageUrl } from "@/plugins/helpers";
import SidebarMenu from "./SidebarMenu.vue";
import SidebarMenuGroup from "./SidebarMenuGroup.vue";

const props = defineProps({
    menus: {
        type: Array as () => SidebarMenuModel[],
        default: () => [],
    },
    responsive: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <aside
        class="fixed top-0 left-0 w-0 h-screen overflow-y-hidden transition-all duration-300 ease-in-out bg-white md:w-64"
        :class="{
            '!static w-full': props.responsive,
        }"
    >
        <nav
            class="h-full"
            :class="{
                '!py-4': props.responsive,
            }"
        >
            <!-- Logo -->
            <div
                v-if="!props.responsive"
                class="flex items-center px-6 py-2.5 mb-1 shrink-0"
            >
                <Link :href="route('home')">
                    <img
                        :src="getImageUrl($page.props.setting.logo)"
                        alt="Logo"
                        class="w-auto h-8 sm:h-12 fill-primary"
                    />
                </Link>
            </div>

            <div
                class="overflow-y-auto h-[calc(100vh-72px)]"
                :class="{
                    'h-[calc(100vh-178px)]': props.responsive,
                }"
            >
                <slot name="extraMenu" />
                <ul>
                    <li v-for="menu in props.menus" :key="menu.name">
                        <SidebarMenuGroup
                            v-if="menu.children"
                            :menu="menu"
                            :responsive="props.responsive"
                        />

                        <SidebarMenu
                            v-else
                            :menu="menu"
                            :responsive="props.responsive"
                        />
                    </li>
                </ul>
            </div>
        </nav>
    </aside>
</template>

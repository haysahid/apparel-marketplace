<script setup lang="ts">
import NavLink from "./NavLink.vue";

const props = defineProps({
    menu: {
        type: Object as () => SidebarMenuModel,
        required: true,
    },
    responsive: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <NavLink
        :href="props.menu.href"
        :active="props.menu.active"
        active-class="!bg-gray-50 !border-primary "
        class="w-full px-6 py-3.5 !text-gray-500 transition-all duration-300 ease-in-out bg-white border-l-4 border-transparent group/inner"
        :class="[
            {
                '!px-4 !py-2.5': props.responsive,
            },
            props.menu.active
                ? 'hover:!bg-gray-50 hover:border-l-4 hover:border-primary !text-primary hover:!text-primary'
                : 'hover:!bg-gray-100 hover:border-l-4 hover:border-primary-light',
        ]"
    >
        <span
            v-if="props.menu.icon"
            v-html="props.menu.icon"
            class="me-3 [&>svg]:fill-gray-400 group/inner-hover:[&>svg]:fill-gray-600 [&>svg]:transition-all [&>svg]:duration-300 [&>svg]:ease-in-out [&>svg]:size-5"
            :class="{
                '[&>svg]:!fill-primary': props.menu.active,
                '[&>svg]:size-6 me-1.5': props.responsive,
            }"
        ></span>
        <p
            class="line-clamp-1 overflow-ellipsis"
            :class="{
                'text-sm': props.responsive,
            }"
        >
            {{ props.menu.name }}
        </p>
    </NavLink>
</template>

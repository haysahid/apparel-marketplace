<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from "vue";
import SidebarMenu from "./SidebarMenu.vue";

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

const active = computed(() =>
    props.menu.children.some((child) => child.active)
);
const expanded = ref(true);

onMounted(() => {
    nextTick(() => {
        if (active.value) {
            expanded.value = true;
        } else {
            expanded.value = false;
        }
    });
});
</script>

<template>
    <details
        class="group hover:bg-gray-50"
        :open="expanded"
        @toggle="expanded = ($event.target as HTMLDetailsElement).open"
    >
        <summary
            class="flex items-center justify-between px-6 py-4 text-base font-medium text-gray-500 border-l-4 border-transparent cursor-pointer select-none"
            :class="{
                'bg-gray-50 border-primary text-primary': active,
                '!px-4 !py-2.5': props.responsive,
            }"
        >
            <div class="flex items-center">
                <span
                    v-if="props.menu.icon"
                    v-html="props.menu.icon"
                    class="me-3 [&>svg]:fill-gray-400 group/inner-hover:[&>svg]:fill-gray-600 [&>svg]:transition-all [&>svg]:duration-300 [&>svg]:ease-in-out [&>svg]:size-5"
                    :class="{
                        '[&>svg]:!fill-primary': active,
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
            </div>
            <span class="ml-2 group-open:rotate-180">
                <svg
                    class="size-4"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                    />
                </svg>
            </span>
        </summary>
        <Transition name="accordion">
            <div v-if="expanded">
                <SidebarMenu
                    v-for="child in menu.children"
                    :key="child.name"
                    :menu="child"
                    :responsive="props.responsive"
                    class="!pl-8 sm:!pl-10"
                />
            </div>
        </Transition>
    </details>
</template>

<script setup>
import { computed, getCurrentInstance } from "vue";
import Dropdown from "./Dropdown.vue";

const props = defineProps({
    hideEditButton: {
        type: Boolean,
        default: false,
    },
    hideDeleteButton: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["edit", "delete"]);

const hasEditCallback = computed(() => {
    return !!getCurrentInstance()?.vnode?.props?.["onEdit"];
});

const hasDeleteCallback = computed(() => {
    return !!getCurrentInstance()?.vnode?.props?.["onDelete"];
});
</script>

<template>
    <div class="flex gap-2">
        <button
            v-if="hasEditCallback && !props.hideEditButton"
            type="button"
            :disabled="props.disabled"
            class="group"
            @click="$emit('edit')"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                class="stroke-blue-500 group-hover:stroke-blue-600 size-5"
            >
                <path
                    d="M11 5.00016H6C5.46957 5.00016 4.96086 5.21088 4.58579 5.58595C4.21071 5.96102 4 6.46973 4 7.00016V18.0002C4 18.5306 4.21071 19.0393 4.58579 19.4144C4.96086 19.7894 5.46957 20.0002 6 20.0002H17C17.5304 20.0002 18.0391 19.7894 18.4142 19.4144C18.7893 19.0393 19 18.5306 19 18.0002V13.0002M17.586 3.58616C17.7705 3.39514 17.9912 3.24278 18.2352 3.13796C18.4792 3.03314 18.7416 2.97797 19.0072 2.97566C19.2728 2.97335 19.5361 3.02396 19.7819 3.12452C20.0277 3.22508 20.251 3.37359 20.4388 3.56137C20.6266 3.74916 20.7751 3.97246 20.8756 4.21825C20.9762 4.46405 21.0268 4.72741 21.0245 4.99296C21.0222 5.25852 20.967 5.52096 20.8622 5.76497C20.7574 6.00898 20.605 6.22967 20.414 6.41416L11.828 15.0002H9V12.1722L17.586 3.58616Z"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </button>

        <button
            v-if="hasDeleteCallback && !props.hideDeleteButton"
            type="button"
            :disabled="props.disabled"
            class="group"
            @click="$emit('delete')"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                class="stroke-red-500 group-hover:stroke-red-600 size-5"
            >
                <path
                    d="M3 6H21M19 6V20C19 21 18 22 17 22H7C6 22 5 21 5 20V6M8 6V4C8 3 9 2 10 2H14C15 2 16 3 16 4V6M10 11V17M14 11V17"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </button>

        <Dropdown
            v-if="$slots.moreContent"
            align="right"
            width="32"
            :showBackdrop="false"
        >
            <template #trigger>
                <button type="button" :disabled="props.disabled" class="group">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        class="transition-colors duration-200 fill-gray-600 size-5 group-hover:fill-gray-700 group-focus:fill-gray-700"
                    >
                        <path
                            d="M12 20C11.45 20 10.9793 19.8043 10.588 19.413C10.1967 19.0217 10.0007 18.5507 10 18C9.99934 17.4493 10.1953 16.9787 10.588 16.588C10.9807 16.1973 11.4513 16.0013 12 16C12.5487 15.9987 13.0197 16.1947 13.413 16.588C13.8063 16.9813 14.002 17.452 14 18C13.998 18.548 13.8023 19.019 13.413 19.413C13.0237 19.807 12.5527 20.0027 12 20ZM12 14C11.45 14 10.9793 13.8043 10.588 13.413C10.1967 13.0217 10.0007 12.5507 10 12C9.99934 11.4493 10.1953 10.9787 10.588 10.588C10.9807 10.1973 11.4513 10.0013 12 10C12.5487 9.99867 13.0197 10.1947 13.413 10.588C13.8063 10.9813 14.002 11.452 14 12C13.998 12.548 13.8023 13.019 13.413 13.413C13.0237 13.807 12.5527 14.0027 12 14ZM12 8.00001C11.45 8.00001 10.9793 7.80434 10.588 7.41301C10.1967 7.02167 10.0007 6.55067 10 6.00001C9.99934 5.44934 10.1953 4.97867 10.588 4.58801C10.9807 4.19734 11.4513 4.00134 12 4.00001C12.5487 3.99867 13.0197 4.19467 13.413 4.58801C13.8063 4.98134 14.002 5.45201 14 6.00001C13.998 6.54801 13.8023 7.01901 13.413 7.41301C13.0237 7.80701 12.5527 8.00267 12 8.00001Z"
                        />
                    </svg>
                </button>
            </template>
            <template #content>
                <slot name="moreContent" />
            </template>
        </Dropdown>
    </div>
</template>

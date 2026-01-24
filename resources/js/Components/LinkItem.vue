<script setup>
import { useSlots } from "vue";
import { getImageUrl } from "@/plugins/helpers";

const props = defineProps({
    name: {
        type: String,
        default: null,
    },
    url: {
        type: String,
        default: null,
    },
    icon: {
        type: String,
        default: null,
    },
    index: {
        type: Number,
        required: true,
    },
    drag: {
        type: Boolean,
        default: false,
    },
    showDeleteButton: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["click", "delete"]);

const slots = useSlots();

const getPlatformNameFromUrl = (url) => {
    return (
        url.match(/^(https?:\/\/)?(www\.)?([^\/]+)/)?.[3] ||
        "Platform Tidak Diketahui"
    );
};
</script>

<template>
    <button
        type="button"
        class="flex items-center justify-start w-full gap-3 pl-3.5 pr-2 py-2.5 bg-white outline outline-gray-200 rounded-xl hover:outline-primary-light transition-all duration-200 ease-in-out cursor-pointer -outline-offset-2"
        :class="{
            'hover:outline-gray-300 ': drag,
            'pr-3.5': !showDeleteButton,
        }"
        @click="emit('click')"
    >
        <slot v-if="slots.icon" name="icon" />
        <img
            v-else-if="props.icon"
            :src="getImageUrl(props.icon)"
            :alt="props.name"
            class="text-blue-500 size-8 !fill-blue-500"
        />
        <div v-else>
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="25"
                height="24"
                viewBox="0 0 25 24"
                class="fill-gray-400 size-8"
            >
                <path
                    d="M12.1398 2C6.62577 2 2.13977 6.486 2.13977 12C2.13977 17.514 6.62577 22 12.1398 22C17.6538 22 22.1398 17.514 22.1398 12C22.1398 6.486 17.6538 2 12.1398 2ZM20.0708 11H17.3068C17.1824 8.81142 16.5701 6.67835 15.5148 4.757C16.7391 5.32882 17.7989 6.2011 18.5955 7.29266C19.3921 8.38422 19.8996 9.65957 20.0708 11ZM12.6698 4.027C13.7048 5.391 15.0968 7.807 15.2968 11H9.16977C9.30877 8.404 10.1638 5.972 11.6208 4.026C11.7928 4.016 11.9648 4 12.1398 4C12.3188 4 12.4938 4.016 12.6698 4.027ZM8.82777 4.727C7.84377 6.618 7.27577 8.762 7.16977 11H4.20877C4.38126 9.64775 4.89602 8.36189 5.70431 7.26416C6.51261 6.16643 7.58768 5.29315 8.82777 4.727ZM4.20877 13H7.18277C7.31877 15.379 7.84777 17.478 8.73877 19.23C7.52096 18.6559 6.4675 17.7842 5.67573 16.6953C4.88396 15.6064 4.37943 14.3355 4.20877 13ZM11.5898 19.973C10.1888 18.275 9.36177 15.896 9.18077 13H15.2938C15.0858 15.773 14.1768 18.196 12.6908 19.972C12.5088 19.984 12.3268 20 12.1398 20C11.9538 20 11.7728 19.984 11.5898 19.973ZM15.6008 19.201C16.5558 17.407 17.1388 15.3 17.2918 13H20.0698C19.9014 14.3244 19.4043 15.5857 18.6238 16.6688C17.8432 17.7519 16.8039 18.6224 15.6008 19.201Z"
                />
            </svg>
        </div>
        <div class="w-full pb-0.5">
            <p class="font-medium text-gray-700 text-start">
                {{ props.name || getPlatformNameFromUrl(props.url) }}
            </p>
            <p
                v-if="props.url"
                class="flex-1 text-xs text-gray-800 line-clamp-1 overflow-ellipsis text-start"
            >
                {{ props.url }}
            </p>
            <p
                v-else
                class="flex-1 text-xs italic text-gray-500 line-clamp-1 overflow-ellipsis text-start"
            >
                {{ "Belum Ada Tautan" }}
            </p>
        </div>

        <button
            v-if="showDeleteButton"
            type="button"
            class="p-[7px] text-gray-400 bg-white rounded-full hover:bg-gray-100 transition-all duration-300 ease-in-out"
            @click="emit('delete')"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="size-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>
    </button>
</template>

<script setup lang="ts">
import { computed } from "vue";
import DiscountTag from "@/Components/DiscountTag.vue";

const props = defineProps({
    name: {
        type: String,
        default: null,
    },
    variant: {
        type: Object as () => ProductVariantEntity,
        required: true,
    },
    showDivider: {
        type: Boolean,
        default: false,
    },
    index: {
        type: Number,
        default: 0,
    },
    drag: {
        type: Boolean,
        default: false,
    },
    showDeleteButton: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["click", "delete"]);

function formatPrice(price = 0) {
    return price.toLocaleString("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    });
}

const isFile = (image) => {
    return image instanceof File;
};

const loadFile = (file) => {
    return URL.createObjectURL(file);
};
</script>

<template>
    <button
        type="button"
        class="flex items-center justify-start w-full gap-3 pl-3.5 pr-2 py-2.5 bg-white outline outline-gray-300 rounded-xl hover:outline-primary-light transition-all duration-200 ease-in-out cursor-pointer -outline-offset-2"
        :class="{
            'hover:outline-gray-300 ': drag,
            'pr-3.5': !showDeleteButton,
        }"
        @click="emit('click')"
    >
        <div
            class="flex items-center justify-center flex-shrink-0 gap-4 max-sm:mt-1"
        >
            <!-- Image -->
            <img
                v-if="props.variant.images.length > 0"
                :src="
                    isFile(props.variant.images[0].image)
                        ? loadFile(props.variant.images[0].image)
                        : props.variant.images[0].image
                "
                alt="Product Image"
                class="object-cover size-[60px] sm:size-[80px]"
            />
            <div
                v-else
                class="flex items-center justify-center size-[60px] sm:size-[80px] bg-gray-100 rounded-lg aspect-square"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    class="size-6 fill-gray-400"
                >
                    <path
                        d="M5 21C4.45 21 3.97933 20.8043 3.588 20.413C3.19667 20.0217 3.00067 19.5507 3 19V5C3 4.45 3.196 3.97933 3.588 3.588C3.98 3.19667 4.45067 3.00067 5 3H19C19.55 3 20.021 3.196 20.413 3.588C20.805 3.98 21.0007 4.45067 21 5V19C21 19.55 20.8043 20.021 20.413 20.413C20.0217 20.805 19.5507 21.0007 19 21H5ZM6 17H18L14.25 12L11.25 16L9 13L6 17Z"
                    />
                </svg>
            </div>
        </div>

        <div class="flex flex-col items-start w-full pb-2">
            <!-- Detail -->
            <p
                class="text-sm font-medium text-gray-800 sm:text-base text-start"
            >
                {{ props.name }}
            </p>

            <p class="text-xs text-gray-800 sm:text-sm">
                {{ formatPrice(props.variant.final_selling_price) }}
            </p>
            <div class="flex items-center gap-x-1">
                <p
                    v-if="props.variant.discount > 0"
                    class="text-xs text-gray-500 line-through"
                >
                    {{ formatPrice(props.variant.base_selling_price) }}
                </p>
                <DiscountTag
                    v-if="props.variant.discount > 0"
                    :discount-type="props.variant.discount_type"
                    :discount="props.variant.discount"
                    class="!text-xs"
                />
            </div>
        </div>

        <button
            v-if="showDeleteButton"
            type="button"
            class="p-[7px] text-gray-400 bg-white rounded-full hover:bg-gray-100 transition-all duration-300 ease-in-out"
            @click.stop="emit('delete')"
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

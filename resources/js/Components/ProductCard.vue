<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import DiscountTag from "@/Components/DiscountTag.vue";

const props = defineProps({
    name: String,
    slug: {
        type: String,
        default: "product-slug",
    },
    basePrice: {
        type: Number,
        default: 0,
    },
    discountType: {
        type: String,
        default: "percentage", // or "fixed"
    },
    discount: {
        type: Number,
        default: null,
    },
    finalPrice: {
        type: Number,
        default: 0,
    },
    image: String,
    description: String,
});

function formatPrice(price) {
    return price.toLocaleString("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    });
}
</script>

<template>
    <Link :href="route('product.show', { slug: props.slug })">
        <div
            class="bg-white rounded-lg outline outline-1 shadow-sm outline-gray-100 hover:outline-primary-light transition-all duration-300 cursor-pointer h-full hover:scale-[1.02] hover:shadow-md"
        >
            <img
                v-if="props.image"
                :src="props.image"
                :alt="props.name"
                class="object-cover w-full rounded-t-lg aspect-square"
            />
            <div
                v-else
                class="flex items-center justify-center bg-gray-100 rounded-t-lg aspect-square"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    class="size-8 fill-gray-400"
                >
                    <path
                        d="M5 21C4.45 21 3.97933 20.8043 3.588 20.413C3.19667 20.0217 3.00067 19.5507 3 19V5C3 4.45 3.196 3.97933 3.588 3.588C3.98 3.19667 4.45067 3.00067 5 3H19C19.55 3 20.021 3.196 20.413 3.588C20.805 3.98 21.0007 4.45067 21 5V19C21 19.55 20.8043 20.021 20.413 20.413C20.0217 20.805 19.5507 21.0007 19 21H5ZM6 17H18L14.25 12L11.25 16L9 13L6 17Z"
                    />
                </svg>
            </div>
            <div
                class="flex flex-col justify-between gap-1 px-3 py-4 md:pb-5 md:pt-4 md:px-5"
            >
                <h3 class="font-bold sm:text-lg line-clamp-2 overflow-ellipsis">
                    {{ props.name }}
                </h3>
                <p
                    class="text-sm text-gray-500 sm:text-base line-clamp-1 overflow-ellipsis"
                >
                    {{ props.description }}
                </p>
                <div
                    class="flex flex-wrap items-center justify-between mt-1 gap-y-1 gap-x-2"
                >
                    <div>
                        <p class="font-semibold sm:text-lg text-primary">
                            {{ formatPrice(props.finalPrice) }}
                        </p>

                        <p
                            v-if="props.discount"
                            class="text-sm text-gray-500 line-through"
                        >
                            {{ formatPrice(props.basePrice) }}
                        </p>
                    </div>

                    <DiscountTag
                        v-if="props.discount"
                        :discount-type="props.discountType"
                        :discount="props.discount"
                    />
                </div>
            </div></div
    ></Link>
</template>

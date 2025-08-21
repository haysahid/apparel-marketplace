<script setup>
import { computed } from "vue";

const props = defineProps({
    discountType: {
        type: String,
        default: "percentage", // or "fixed"
    },
    discount: {
        type: Number,
        default: null,
    },
});

const discountText = computed(() => {
    if (props.discountType === "percentage") {
        return `${props.discount}%`;
    } else if (props.discountType === "fixed") {
        return `Rp ${props.discount.toLocaleString("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        })}`;
    }
    return null;
});
</script>

<template>
    <div
        class="px-1.5 py-0.5 text-xs sm:text-sm text-white bg-red-500 rounded-md h-fit font-normal sm:font-medium w-fit"
    >
        {{ discountText }}
    </div>
</template>

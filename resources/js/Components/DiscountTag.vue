<script setup lang="ts">
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
        class="px-2 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-md"
    >
        {{ discountText }}
    </div>
</template>

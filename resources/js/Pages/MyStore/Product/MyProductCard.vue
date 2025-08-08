<script setup lang="ts">
import AdminItemAction from "@/Components/AdminItemAction.vue";
import { computed } from "vue";
import { formatCurrency } from "@/plugins/number-formatter";
import DiscountTag from "@/Components/DiscountTag.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

const props = defineProps({
    product: {
        type: Object as () => ProductEntity,
        required: true,
    },
});

const emit = defineEmits(["edit", "delete"]);

const basePrice = computed(() => {
    if (
        props.product.lowest_base_selling_price ===
        props.product.highest_base_selling_price
    ) {
        return formatCurrency(props.product.lowest_base_selling_price);
    }

    return `${formatCurrency(
        props.product.lowest_base_selling_price
    )} - ${formatCurrency(props.product.highest_base_selling_price)}`;
});

const finalPrice = computed(() => {
    if (
        props.product.lowest_final_selling_price ===
        props.product.highest_final_selling_price
    ) {
        return formatCurrency(props.product.lowest_final_selling_price);
    }

    return `${formatCurrency(
        props.product.lowest_final_selling_price
    )} - ${formatCurrency(props.product.highest_final_selling_price)}`;
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-between gap-2 p-2.5 sm:gap-3 sm:p-4 transition-all duration-300 ease-in-out border border-gray-200 rounded-lg hover:border-primary-light hover:ring-1 hover:ring-primary-light relative"
    >
        <div class="flex items-start w-full gap-2.5 sm:gap-3">
            <img
                v-if="props.product.images.length > 0"
                :src="(props.product.images[0].image as string)"
                :alt="props.product.name"
                class="object-cover h-[80px] sm:h-[100px] aspect-square rounded border border-gray-200 self-start"
            />
            <div
                v-else
                class="flex items-center justify-center h-[80px] sm:h-[100px] bg-gray-100 rounded aspect-square"
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

            <div class="flex w-full gap-2">
                <div
                    class="flex flex-col items-start justify-start w-full gap-0.5 sm:gap-1"
                >
                    <p
                        class="text-sm font-medium text-gray-900 md:text-base pe-12"
                    >
                        {{ props.product.name }}
                    </p>
                    <p class="text-xs text-gray-500 pe-12">
                        {{ props.product.brand.name }}
                    </p>
                    <div class="flex items-start w-full gap-4 mt-1">
                        <div class="flex flex-col items-start w-full">
                            <p class="text-sm font-medium text-gray-600">
                                {{ finalPrice }}
                            </p>
                            <div
                                v-if="props.product.discount"
                                class="flex items-center gap-2"
                            >
                                <p class="text-xs text-gray-500 line-through">
                                    {{ basePrice }}
                                </p>
                                <DiscountTag
                                    v-if="props.product.discount"
                                    :discount-type="props.product.discount_type"
                                    :discount="props.product.discount"
                                    class="!px-1 !py-0 !rounded-md !text-[10px] !font-medium"
                                />
                            </div>
                        </div>
                        <div
                            class="flex items-center gap-2 py-1.5 px-2 bg-gray-100 rounded-md"
                        >
                            <p class="text-sm font-medium text-gray-600">
                                {{
                                    props.product.variants.reduce(
                                        (total, variant) =>
                                            total + variant.current_stock_level,
                                        0
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <AdminItemAction
            class="absolute top-2.5 right-2.5 sm:top-4 sm:right-4"
        >
            <template #moreContent>
                <div class="divide-y divide-gray-200">
                    <DropdownLink as="button" @click="emit('edit')">
                        Ubah
                    </DropdownLink>
                    <DropdownLink as="button" @click="emit('delete')">
                        Hapus
                    </DropdownLink>
                </div>
            </template>
        </AdminItemAction>
    </div>
</template>

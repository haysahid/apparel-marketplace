<script setup lang="ts">
import DefaultCard from "@/Components/DefaultCard.vue";
import CartGroup from "@/Pages/Cart/CartGroup.vue";
import { ref, computed } from "vue";
import AddOrderCartItem from "./AddOrderCartItem.vue";
import QuantityInput from "@/Components/QuantityInput.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";

const cartGroup = ref<CartGroupModel>(null);

function addItem(item: CartItemModel) {
    if (!cartGroup.value) {
        cartGroup.value = {
            store: item.store,
            items: [],
        } as CartGroupModel;
    }
    cartGroup.value.items.push(item);
}

function updateItem(index: number, item: CartItemModel) {
    if (cartGroup.value) {
        cartGroup.value.items[index] = item;
    }
}

function removeItem(index: number) {
    if (cartGroup.value) {
        cartGroup.value.items.splice(index, 1);
        if (cartGroup.value.items.length === 0) {
            cartGroup.value = null;
        }
    }
}

defineExpose({
    cartGroup,
    addItem,
});
</script>

<template>
    <DefaultCard>
        <h2 class="mb-4 font-semibold">
            Keranjang ({{ cartGroup?.items.length || 0 }})
        </h2>
        <div
            v-if="!cartGroup"
            class="text-sm text-center text-gray-500 h-[50vh] flex items-center justify-center"
        >
            Belum ada produk yang ditambahkan ke keranjang.
        </div>
        <div v-else class="flex flex-col w-full">
            <template v-for="(item, index) in cartGroup.items" :key="index">
                <AddOrderCartItem :item="item">
                    <template #actions>
                        <div
                            v-if="item.variant"
                            class="flex items-center justify-between w-full gap-4 xl:flex-col xl:items-end xl:justify-normal xl:w-fit"
                        >
                            <button
                                class="text-xs text-gray-500 sm:block sm:text-sm hover:text-red-500 sm:w-fit text-start"
                                @click="item.showDeleteConfirmation = true"
                            >
                                Hapus
                            </button>

                            <div
                                class="flex items-center justify-end w-full gap-y-2 gap-x-4 xl:flex-col xl:items-end xl:w-fit"
                            >
                                <QuantityInput
                                    :modelValue="item.quantity"
                                    :unit="item.variant.unit.name"
                                    :max="item.variant.current_stock_level"
                                    :showAvailability="false"
                                    :label="null"
                                    @update:modelValue="
                                        updateItem(index, {
                                            ...item,
                                            quantity: $event,
                                        })
                                    "
                                />

                                <p
                                    class="text-sm font-semibold text-gray-800 text-end w-[110px]"
                                >
                                    {{
                                        $formatCurrency(
                                            item.variant.final_selling_price *
                                                item.quantity
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </template>
                </AddOrderCartItem>

                <DeleteConfirmationDialog
                    :show="item.showDeleteConfirmation"
                    :item="item"
                    title="Hapus produk ini dari keranjang?"
                    :description="item.variant?.name"
                    positiveButtonText="Hapus"
                    @delete="
                        removeItem(index);
                        item.showDeleteConfirmation = false;
                    "
                    @close="item.showDeleteConfirmation = false"
                />
            </template>
        </div>
    </DefaultCard>
</template>

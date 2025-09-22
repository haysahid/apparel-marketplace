<script setup lang="ts">
import DefaultCard from "@/Components/DefaultCard.vue";
import { ref } from "vue";
import AddOrderCartItem from "./AddOrderCartItem.vue";
import QuantityInput from "@/Components/QuantityInput.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { useCartStore } from "@/stores/cart-store";

const cartStore = useCartStore();
const emit = defineEmits(["next", "addItem"]);

const showClearCartConfirmation = ref(false);
</script>

<template>
    <DefaultCard :isMain="true" class="!p-0">
        <div
            class="flex items-center justify-between gap-4 px-4 pt-4 sm:pt-6 sm:px-6"
        >
            <h2 class="font-semibold">
                Keranjang ({{ cartStore.items.length || 0 }})
            </h2>
            <div
                v-if="cartStore.items.length > 0"
                class="flex items-center gap-4"
            >
                <button
                    type="button"
                    class="text-sm text-gray-500 hover:text-red-500 hover:underline"
                    @click="showClearCartConfirmation = true"
                >
                    Kosongkan
                </button>
                <PrimaryButton @click="emit('addItem')" class="lg:hidden">
                    Tambah
                </PrimaryButton>
            </div>
        </div>
        <div class="px-4 sm:px-6">
            <div
                v-if="cartStore.syncStatus == 'loading'"
                class="flex flex-col w-full"
            >
                <div
                    v-for="n in cartStore.items.length || 3"
                    :key="n"
                    class="flex items-end gap-4 py-2.5 sm:py-4 bg-white animate-pulse border-b border-gray-200"
                >
                    <div class="flex items-center justify-center w-full gap-4">
                        <div
                            class="bg-gray-100 rounded-lg size-[60px] sm:size-[80px]"
                        ></div>
                        <div class="flex flex-col flex-1 gap-2.5">
                            <div class="w-3/4 h-4 bg-gray-100 rounded-md"></div>
                            <div class="w-2/4 h-4 bg-gray-100 rounded-md"></div>
                            <div class="w-1/3 h-4 bg-gray-100 rounded-md"></div>
                        </div>
                    </div>
                    <div class="w-12 h-4 bg-gray-100 rounded-md"></div>
                </div>
            </div>
            <div
                v-else-if="!cartStore.items.length"
                class="flex flex-col items-center justify-center gap-4 py-16"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    class="text-gray-400 size-16"
                >
                    <path
                        d="M17 18C17.5304 18 18.0391 18.2107 18.4142 18.5858C18.7893 18.9609 19 19.4696 19 20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22C16.4696 22 15.9609 21.7893 15.5858 21.4142C15.2107 21.0391 15 20.5304 15 20C15 18.89 15.89 18 17 18ZM1 2H4.27L5.21 4H20C20.2652 4 20.5196 4.10536 20.7071 4.29289C20.8946 4.48043 21 4.73478 21 5C21 5.17 20.95 5.34 20.88 5.5L17.3 11.97C16.96 12.58 16.3 13 15.55 13H8.1L7.2 14.63L7.17 14.75C7.17 14.8163 7.19634 14.8799 7.24322 14.9268C7.29011 14.9737 7.3537 15 7.42 15H19V17H7C6.46957 17 5.96086 16.7893 5.58579 16.4142C5.21071 16.0391 5 15.5304 5 15C5 14.65 5.09 14.32 5.24 14.04L6.6 11.59L3 4H1V2ZM7 18C7.53043 18 8.03914 18.2107 8.41421 18.5858C8.78929 18.9609 9 19.4696 9 20C9 20.5304 8.78929 21.0391 8.41421 21.4142C8.03914 21.7893 7.53043 22 7 22C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20C5 18.89 5.89 18 7 18ZM16 11L18.78 6H6.14L8.5 11H16Z"
                        fill="currentColor"
                    />
                </svg>

                <p class="text-sm text-center text-gray-500">
                    Belum ada produk yang ditambahkan ke keranjang.
                </p>

                <PrimaryButton @click="emit('addItem')" class="lg:hidden">
                    Tambah Produk
                </PrimaryButton>
            </div>
            <div v-else class="w-full">
                <div v-for="(item, index) in cartStore.items" :key="index">
                    <AddOrderCartItem :item="item">
                        <template #actions>
                            <div
                                v-if="item.variant"
                                class="flex items-center justify-between w-full gap-4 xl:flex-col xl:items-end xl:justify-normal xl:w-fit"
                            >
                                <button
                                    class="text-xs text-gray-500 sm:block sm:text-sm hover:text-red-500 sm:w-fit text-start hover:underline"
                                    @click="item.showDeleteConfirmation = true"
                                >
                                    Hapus
                                </button>

                                <div
                                    class="flex items-center justify-end w-full gap-y-2 gap-x-4 xl:flex-col xl:items-end xl:w-fit"
                                >
                                    <QuantityInput
                                        :modelValue="item.quantity"
                                        :unit="item.variant.unit?.name"
                                        :max="item.variant.current_stock_level"
                                        :showAvailability="false"
                                        :label="null"
                                        @update:modelValue="
                                            cartStore.updateItem({
                                                ...item,
                                                quantity: parseInt($event),
                                            })
                                        "
                                    />

                                    <p
                                        class="text-sm font-semibold text-gray-800 text-end w-[110px]"
                                    >
                                        {{
                                            $formatCurrency(
                                                item.variant
                                                    .final_selling_price *
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
                            cartStore.removeItem(item);
                            item.showDeleteConfirmation = false;
                        "
                        @close="item.showDeleteConfirmation = false"
                    />
                </div>
            </div>
        </div>
        <div
            class="sticky bottom-0 flex flex-col gap-2 px-4 pt-4 pb-4 bg-white rounded-b-lg sm:pb-6 sm:px-6 sm:bg-transparent sm:static"
        >
            <div class="flex items-center justify-between">
                <p class="font-bold text-gray-700">Sub Total</p>
                <p class="font-bold text-primary">
                    {{ $formatCurrency(cartStore.subTotal) }}
                </p>
            </div>
            <PrimaryButton
                class="w-full py-3 mt-2"
                :disabled="cartStore.items.length == 0"
                @click="emit('next')"
            >
                Selanjutnya
            </PrimaryButton>
        </div>

        <DeleteConfirmationDialog
            :show="showClearCartConfirmation"
            title="Kosongkan keranjang?"
            :description="'Semua produk dalam keranjang akan dihapus.'"
            positiveButtonText="Hapus"
            @delete="
                cartStore.clearCart();
                showClearCartConfirmation = false;
            "
            @close="showClearCartConfirmation = false"
        />
    </DefaultCard>
</template>

<script setup lang="ts">
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import OrderProductSelection from "./OrderProductSelection.vue";
import AddOrderCart from "./AddOrderCart.vue";
import DialogModal from "@/Components/DialogModal.vue";
import { ref } from "vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import axios from "axios";
import OrderVariantSelection from "./OrderVariantSelection.vue";

const highlightedProduct = ref(null);

const showVariantModal = ref(false);

const getProductDetailStatus = ref(null);

function getProductDetail(productId) {
    getProductDetailStatus.value = "loading";
    axios
        .get(`/api/my-store/product/${productId}`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem("access_token")}`,
            },
        })
        .then((response) => {
            highlightedProduct.value = response.data.result;
            getProductDetailStatus.value = "success";
        })
        .catch((error) => {
            console.error("Error fetching product details:", error);
            openErrorDialog("Gagal mengambil detail produk.");
            getProductDetailStatus.value = "error";
        });
}

const openVariantModal = (product) => {
    getProductDetail(product.id);
    showVariantModal.value = true;
};

const closeVariantModal = () => {
    showVariantModal.value = false;
};

const showSuccessDialog = ref(false);
const successMessage = ref("Data Berhasil Dihapus");

const openSuccessDialog = (message) => {
    successMessage.value = message;
    showSuccessDialog.value = true;
};

const showErrorDialog = ref(false);
const errorMessage = ref("");

const openErrorDialog = (message) => {
    errorMessage.value = message;
    showErrorDialog.value = true;
};

const orderCart = ref(null);
</script>

<template>
    <MyStoreLayout
        title="Tambah Pesanan"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Pesanan', url: '/my-store/order', active: false },
            { text: 'Tambah Pesanan', active: true },
        ]"
    >
        <div class="flex flex-col gap-6 md:flex-row">
            <OrderProductSelection
                class="w-full"
                @selectProduct="openVariantModal($event)"
            />
            <AddOrderCart ref="orderCart" class="w-full" />
        </div>

        <DialogModal
            :show="showVariantModal"
            :closeable="true"
            @close="closeVariantModal()"
            dialogClass="overflow-y-hidden"
            containerClass="!px-0"
            maxWidth="5xl"
        >
            <template #content>
                <OrderVariantSelection
                    :product="{
                        ...highlightedProduct,
                        variants: undefined,
                    }"
                    :variants="highlightedProduct?.variants || []"
                    :isLoading="getProductDetailStatus === 'loading'"
                    :selectedVariants="
                        orderCart.cartGroup?.items.map(
                            (item) => item.variant
                        ) || []
                    "
                    @selectVariant="
                        (variant) => {
                            if (variant) {
                                orderCart.addItem({
                                    id: Date.now(),
                                    variant: variant,
                                    quantity: 1,
                                    image: variant.images[0].image || null,
                                });
                                closeVariantModal();
                            }
                        }
                    "
                />
            </template>
        </DialogModal>

        <SuccessDialog
            :show="showSuccessDialog"
            :title="successMessage"
            @close="showSuccessDialog = false"
        />

        <ErrorDialog :show="showErrorDialog" @close="showErrorDialog = false">
            <template #content>
                <div>
                    <div
                        class="mb-1 text-lg font-medium text-center text-gray-900"
                    >
                        Terjadi Kesalahan
                    </div>
                    <p class="text-center text-gray-700">
                        {{ errorMessage }}
                    </p>
                </div>
            </template>
        </ErrorDialog>
    </MyStoreLayout>
</template>

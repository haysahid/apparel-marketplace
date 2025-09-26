<script setup lang="ts">
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import OrderProductSelection from "./OrderProductSelection.vue";
import AddOrderCart from "./AddOrderCart.vue";
import DialogModal from "@/Components/DialogModal.vue";
import { computed, onMounted, ref } from "vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import axios from "axios";
import OrderVariantSelection from "./OrderVariantSelection.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import Stepper from "@/Components/Stepper.vue";
import CheckoutGroup from "@/Pages/Cart/CheckoutGroup.vue";
import DetailRow from "@/Components/DetailRow.vue";
import AddOrderForm from "./AddOrderForm.vue";
import { useCartStore } from "@/stores/cart-store";
import { useScreenSize } from "@/plugins/screen-size";
import GuestForm from "@/Pages/Cart/GuestForm.vue";
import { usePage } from "@inertiajs/vue3";
import { useOrderStore } from "@/stores/order-store";
import BaseDialog from "@/Components/BaseDialog.vue";
import OrderCustomerSelection from "./OrderCustomerSelection.vue";
import CustomPageProps from "@/types/model/CustomPageProps";

const screenSize = useScreenSize();

const cartStore = useCartStore();

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

function addToCart(variant) {
    const existingCartItem = cartStore.getItemByVariant(variant);

    if (existingCartItem) return;

    const cartItem: CartItemModel = {
        store_id: highlightedProduct.value.store_id,
        store: highlightedProduct.value.store,
        product_id: highlightedProduct.value.id,
        variant_id: variant.id,
        quantity: 1,
        image:
            variant.images[0]?.image ||
            highlightedProduct.value.images[0]?.image,
        variant: variant,
        created_at: new Date().toISOString(),
        selected: true,
    };

    const cartGroup: CartGroupModel = {
        store_id: highlightedProduct.value.store_id,
        store: highlightedProduct.value.store,
        items: [cartItem],
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        selected: true,
        showDeleteConfirmation: false,
    };

    cartStore.addGroup(cartGroup);
}

const showProductSelectionModal = ref(false);

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

onMounted(() => {
    cartStore.syncCart();
});

const stepIndex = ref(0);

const steps = computed(() => [
    {
        title: "Pilih Produk",
        subtitle: null,
        disabled: false,
    },
    {
        title: "Detail Pesanan",
        subtitle: null,
        disabled: cartStore.items.length === 0,
    },
]);

const scrollToTop = () => {
    const productListElement = document.querySelector("#main-area");
    if (productListElement) {
        productListElement.scrollTo({ top: 0, behavior: "smooth" });
    }
};

const page = usePage<CustomPageProps>();
const orderStore = useOrderStore();

const customer = computed(() => {
    if (orderStore.form.customer) {
        return {
            ...orderStore.form.customer,
            type: orderStore.form.customer.role?.name,
        };
    }

    if (orderStore.form.guest_name) {
        return {
            name: orderStore.form.guest_name,
            email: orderStore.form.guest_email,
            phone: orderStore.form.guest_phone,
            type: "Tamu Baru",
        };
    }

    return {
        name: page.props.auth.user.name,
        email: page.props.auth.user.email,
        phone: page.props.auth.user.phone,
        type: page.props.auth.user.role?.name,
        is_default: true,
    };
});

const showCustomerOptionModal = ref(false);
const showCustomerSelectionModal = ref(false);

const showGuestForm = ref(false);
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
        <div class="flex flex-col sm:gap-4">
            <!-- Tab Navigation -->
            <DefaultCard
                :isMain="true"
                class="!py-0 !px-4 sticky top-0 z-10 sm:static"
            >
                <Stepper :steps="steps" v-model:stepIndex="stepIndex" />
            </DefaultCard>

            <div>
                <!-- Step 1 -->
                <div
                    v-if="stepIndex === 0"
                    class="flex flex-col lg:gap-4 lg:flex-row"
                >
                    <OrderProductSelection
                        v-show="screenSize.is('lg')"
                        class="w-full"
                        @selectProduct="openVariantModal($event)"
                    />
                    <AddOrderCart
                        class="w-full h-fit"
                        @next="
                            stepIndex++;
                            scrollToTop();
                        "
                        @addItem="showProductSelectionModal = true"
                    />

                    <DialogModal
                        :show="showProductSelectionModal"
                        @close="showProductSelectionModal = false"
                        dialogClass="overflow-y-hidden"
                        containerClass="!p-0"
                    >
                        <template #content>
                            <OrderProductSelection
                                :isModal="true"
                                class="w-full"
                                @selectProduct="openVariantModal($event)"
                            />
                        </template>
                    </DialogModal>
                </div>

                <!-- Step 2 -->
                <div v-else-if="stepIndex === 1" class="w-full">
                    <div
                        class="flex flex-col items-center justify-center w-full mx-auto gap-y-1.5 gap-x-4 lg:flex-row lg:items-start"
                    >
                        <!-- Cart Items -->
                        <DefaultCard :isMain="true" class="!p-0 w-full">
                            <CheckoutGroup
                                v-for="group in cartStore.groups"
                                :cartGroup="group"
                                class="border-none outline-none hover:outline-none"
                            />
                        </DefaultCard>

                        <!-- Detail Order -->
                        <div
                            class="flex flex-col w-full gap-1.5 lg:gap-4 lg:max-w-sm"
                        >
                            <DefaultCard :isMain="true" class="!p-4">
                                <div class="flex flex-col w-full gap-y-3">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <h3 class="font-semibold text-gray-800">
                                            Data Pemesan
                                            <span
                                                v-if="customer.is_default"
                                                class="text-xs italic font-normal text-gray-500"
                                            >
                                                - Otomatis</span
                                            >
                                        </h3>
                                        <div class="flex items-center gap-5">
                                            <button
                                                v-if="!customer.is_default"
                                                class="text-sm text-red-600 hover:underline"
                                                @click="
                                                    orderStore.updateForm({
                                                        ...orderStore.form,
                                                        customer: null,
                                                        guest_name: null,
                                                        guest_email: null,
                                                        guest_phone: null,
                                                    })
                                                "
                                            >
                                                Hapus
                                            </button>
                                            <button
                                                class="text-sm text-blue-500 hover:underline"
                                                @click="
                                                    showCustomerSelectionModal = true
                                                "
                                            >
                                                Ubah
                                            </button>
                                        </div>
                                    </div>
                                    <DetailRow
                                        name="Nama"
                                        :value="customer?.name"
                                    />
                                    <DetailRow
                                        name="Email"
                                        :value="customer?.email"
                                    />
                                    <DetailRow
                                        name="No. HP"
                                        :value="customer?.phone"
                                    />
                                    <DetailRow
                                        name="Jenis"
                                        :value="customer?.type"
                                    />
                                </div>
                            </DefaultCard>

                            <DefaultCard :isMain="true" class="!p-4">
                                <AddOrderForm
                                    @back="
                                        stepIndex = 0;
                                        scrollToTop();
                                    "
                                />
                            </DefaultCard>
                        </div>
                    </div>

                    <DialogModal
                        :show="showGuestForm"
                        maxWidth="sm"
                        @close="showGuestForm = false"
                    >
                        <template #content>
                            <GuestForm
                                :isEdit="true"
                                @submit="showGuestForm = false"
                            />
                        </template>
                    </DialogModal>

                    <DialogModal
                        :show="showCustomerSelectionModal"
                        @close="showCustomerSelectionModal = false"
                        dialogClass="overflow-y-hidden"
                        containerClass="!p-0"
                    >
                        <template #content>
                            <OrderCustomerSelection
                                :isModal="true"
                                :selectedCustomer="orderStore.form.customer"
                                :isGuest="!!orderStore.form.guest_name"
                                class="w-full"
                                @selectCustomer="
                                    (customer) => {
                                        orderStore.updateForm({
                                            ...orderStore.form,
                                            customer: customer,
                                            guest_name: null,
                                            guest_email: null,
                                            guest_phone: null,
                                        });
                                        showCustomerSelectionModal = false;
                                    }
                                "
                                @saveGuest="
                                    showCustomerSelectionModal = false;
                                    orderStore.updateForm({
                                        ...orderStore.form,
                                        customer: null,
                                        guest_name: orderStore.form.guest_name,
                                        guest_email:
                                            orderStore.form.guest_email,
                                        guest_phone:
                                            orderStore.form.guest_phone,
                                    });
                                "
                            />
                        </template>
                    </DialogModal>
                </div>
            </div>
        </div>

        <!-- Customer Option Modal -->
        <BaseDialog
            :show="showCustomerOptionModal"
            title="Jenis Pemesan"
            description="Silakan pilih jenis pemesan."
            positiveButtonText="Pengguna Terdaftar"
            negativeButtonText="Tamu"
            :reverseButton="true"
            @close="showCustomerOptionModal = false"
            @positiveClicked="
                showCustomerOptionModal = false;
                $inertia.visit(
                    route('login', {
                        redirect: route('my-cart'),
                    })
                );
            "
            @negativeClicked="showGuestForm = true"
        />

        <!-- Variant Selection Modal -->
        <DialogModal
            :show="showVariantModal"
            :closeable="true"
            @close="closeVariantModal()"
            dialogClass="overflow-y-hidden"
            containerClass="!p-0"
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
                        cartStore.items.map((item) => item.variant) || []
                    "
                    @selectVariant="
                        (variant) => {
                            if (variant) {
                                addToCart(variant);
                                closeVariantModal();
                                showProductSelectionModal = false;
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

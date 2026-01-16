<script setup lang="ts">
import { ref, onMounted, computed, nextTick } from "vue";
import { usePage, useForm, router } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import DiscountTag from "@/Components/DiscountTag.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import MyProductCard from "./MyProductCard.vue";
import DefaultTable from "@/Components/DefaultTable.vue";
import { useScreenSize } from "@/plugins/screen-size";
import { formatCurrency } from "@/plugins/number-formatter";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import { getImageUrl } from "@/plugins/helpers";
import CustomPageProps from "@/types/model/CustomPageProps";
import SearchInput from "@/Components/SearchInput.vue";
import { scrollToTop } from "@/plugins/helpers";

const screenSize = useScreenSize();

const props = defineProps({
    products: {
        type: Object as () => PaginationModel<ProductEntity>,
        required: true,
    },
    brands: {
        type: Array as () => BrandEntity[],
        required: true,
    },
});

const page = usePage<CustomPageProps>();

const products = ref<PaginationModel<ProductEntity>>({
    ...props.products,
    data: props.products.data.map((product) => ({
        ...product,
        showDeleteModal: false,
    })),
});

const basePrice = (product) => {
    if (
        product.lowest_base_selling_price === product.highest_base_selling_price
    ) {
        return formatCurrency(product.lowest_base_selling_price);
    }
    return `${formatCurrency(
        product.lowest_base_selling_price
    )} - ${formatCurrency(product.highest_base_selling_price)}`;
};

const finalPrice = (product) => {
    if (
        product.lowest_final_selling_price ===
        product.highest_final_selling_price
    ) {
        return formatCurrency(product.lowest_final_selling_price);
    }
    return `${formatCurrency(
        product.lowest_final_selling_price
    )} - ${formatCurrency(product.highest_final_selling_price)}`;
};

const selectedProduct = ref(null);
const showDeleteProductDialog = ref(false);

const openDeleteProductDialog = (product) => {
    if (product) {
        selectedProduct.value = product;
        showDeleteProductDialog.value = true;
    }
};

const closeDeleteProductDialog = (result = false) => {
    showDeleteProductDialog.value = false;
    if (result) {
        selectedProduct.value = null;
        openSuccessDialog("Data Berhasil Dihapus");
    }
};

const deleteProduct = () => {
    if (selectedProduct.value) {
        const form = useForm({});
        form.delete(
            route("my-store.product.destroy", {
                product: selectedProduct.value,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeleteProductDialog(true);
                    getProducts();
                },
            }
        );
    }
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

// Filters
const brands = (page.props.brands || []) as BrandEntity[];
const brandSearch = ref("");
const isBrandDropdownOpen = ref(false);

const filteredBrands = computed(() => {
    return brands.filter((brand) =>
        brand.name
            .toLowerCase()
            .includes(brandSearch.value?.toLowerCase() || "")
    );
});

const filters = useForm({
    page: null,
    search: null,
    brand_id: null,
    brand: null,
});

const getQueryParams = () => {
    filters.page = parseInt(route().params.page) || null;
    filters.search = route().params.search || null;
    filters.brand_id = parseInt(route().params.brand_id) || null;
    filters.brand =
        props.brands.find((brand) => brand.id === filters.brand_id) || null;
};
getQueryParams();

const queryParams = computed(() => {
    return {
        page: filters.page || undefined,
        search: filters.search || undefined,
        brand_id: filters.brand_id || undefined,
    };
});

function getProducts() {
    router.get(route("my-store.product.index"), queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            products.value = {
                ...page.props.products,
                data: page.props.products.data.map((product) => ({
                    ...product,
                    showDeleteModal: false,
                })),
            };
            scrollToTop({ id: "main-area" });
            setSearchFocus();
        },
    });
}

function setSearchFocus() {
    nextTick(() => {
        const input = document.getElementById(
            "search-product"
        ) as HTMLInputElement;
        input?.focus({ preventScroll: true });
    });
}

onMounted(() => {
    if (page.props.flash.success) {
        openSuccessDialog(page.props.flash.success);
    }
    setSearchFocus();
});
</script>

<template>
    <MyStoreLayout title="Produk" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="$inertia.visit(route('my-store.product.create'))"
                >
                    Tambah
                </PrimaryButton>
                <div class="flex items-center gap-2">
                    <DropdownSearchInput
                        id="brand_id"
                        :modelValue="
                            filters.brand_id
                                ? {
                                      label: filters.brand?.name,
                                      value: filters.brand_id,
                                  }
                                : null
                        "
                        :options="
                            filteredBrands.map((brand) => ({
                                label: brand.name,
                                value: brand.id,
                            }))
                        "
                        placeholder="Pilih Brand"
                        class="max-w-48"
                        :autoResize="true"
                        :error="filters.errors.brand_id"
                        @update:modelValue="
                            (option) => {
                                filters.brand_id = option?.value;
                                filters.brand = option
                                    ? filteredBrands.find(
                                          (brand) => brand.id === option.value
                                      )
                                    : null;
                                filters.page = 1;
                                getProducts();
                            }
                        "
                        @search="brandSearch = $event"
                        @clear="
                            filters.brand_id = null;
                            filters.brand = null;
                            brandSearch = '';
                            filters.page = 1;
                            getProducts();
                        "
                    />
                    <SearchInput
                        id="search-product"
                        v-model="filters.search"
                        placeholder="Cari produk..."
                        class="max-w-48"
                        @search="
                            filters.page = 1;
                            getProducts();
                        "
                    />
                </div>
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="products.data.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th class="min-w-[120px] w-[120px]">Foto</th>
                        <th>Nama Barang</th>
                        <th>Harga</th>
                        <th class="w-12">Brand</th>
                        <th>Stok</th>
                        <th class="w-24">Aksi</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr
                        v-for="(product, index) in products.data"
                        :key="product.id"
                    >
                        <td>
                            {{
                                index +
                                1 +
                                (props.products.current_page - 1) *
                                    props.products.per_page
                            }}
                        </td>
                        <td>
                            <img
                                v-if="product.images.length > 0"
                                :src="getImageUrl(product.images[0].image as string)"
                                :alt="product.name"
                                class="object-cover h-[60px] sm:h-[80px] aspect-square rounded border border-gray-200"
                            />
                            <div
                                v-else
                                class="flex items-center justify-center h-[60px] sm:h-[80px] bg-gray-100 rounded aspect-square"
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
                        </td>
                        <td class="!whitespace-normal">
                            {{ product.name }}
                        </td>
                        <td>
                            <p>
                                {{ finalPrice(product) }}
                            </p>
                            <div
                                v-if="product.discount > 0"
                                class="flex items-center justify-start gap-2 mt-1"
                            >
                                <DiscountTag
                                    v-if="product.discount > 0"
                                    :discount-type="product.discount_type"
                                    :discount="product.discount"
                                    class="!text-xs !px-1.5 !py-0.5"
                                />
                                <p class="text-sm text-gray-500 line-through">
                                    {{ basePrice(product) }}
                                </p>
                            </div>
                        </td>
                        <td>
                            {{ product.brand.name }}
                        </td>
                        <td class="text-center">
                            {{ product.stock_count }}
                        </td>
                        <td>
                            <AdminItemAction
                                @edit="
                                    $inertia.visit(
                                        route('my-store.product.edit', {
                                            product: product,
                                        })
                                    )
                                "
                                @delete="openDeleteProductDialog(product)"
                            />
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <template v-if="products.data.length > 0">
                    <div
                        v-for="(product, index) in products.data"
                        :key="product.id"
                    >
                        <MyProductCard
                            :product="product"
                            @edit="
                                $inertia.visit(
                                    route('my-store.product.edit', {
                                        product: product,
                                    })
                                )
                            "
                            @delete="openDeleteProductDialog(product)"
                        />
                    </div>
                </template>
                <div v-else class="flex items-center justify-center py-10">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="products.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ products.from }} - {{ products.to }} dari
                    {{ products.total }} item
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="products.links"
                    @change="
                        (page) => {
                            filters.page = page;
                            getProducts();
                        }
                    "
                />
            </div>

            <DeleteConfirmationDialog
                :show="showDeleteProductDialog"
                :title="`Hapus Produk <b>${selectedProduct?.name}</b>?`"
                @close="closeDeleteProductDialog()"
                @delete="deleteProduct()"
            />

            <SuccessDialog
                :show="showSuccessDialog"
                :title="successMessage"
                @close="showSuccessDialog = false"
            />

            <ErrorDialog
                :show="showErrorDialog"
                @close="showErrorDialog = false"
            >
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
        </DefaultCard>
    </MyStoreLayout>
</template>

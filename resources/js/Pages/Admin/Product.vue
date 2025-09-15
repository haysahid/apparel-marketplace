<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { usePage, useForm, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminPagination from "@/Components/AdminPagination.vue";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import TextInput from "@/Components/TextInput.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DiscountTag from "@/Components/DiscountTag.vue";
import { getImageUrl } from "@/plugins/helpers";

const props = defineProps({
    products: {
        type: Object as () => {
            data: ProductEntity[];
            current_page: number;
            per_page: number;
            total: number;
            links: Array<{ url: string; label: string; active: boolean }>;
        },
        required: true,
    },
    brands: {
        type: Array as () => BrandEntity[],
        required: true,
    },
});

const page = usePage();

const products = ref(
    props.products.data.map((product) => ({
        ...product,
        showDeleteModal: false,
    }))
);

const showDeleteProductDialog = (id) => {
    const product = products.value.find((item) => item.id === id);
    if (product) {
        product.showDeleteModal = true;
        console.log(`Deleting product with ID: ${product}`);
    }
};

const closeDeleteProductDialog = (product, result) => {
    if (product) {
        product.showDeleteModal = false;
        if (result) {
            openSuccessDialog("Data Berhasil Dihapus");
            products.value = products.value.filter(
                (item) => item.id !== product.id
            );
        }
    }
};

const deleteProduct = (product) => {
    if (product) {
        const form = useForm();
        form.delete(
            route("admin.product.destroy", {
                product: product,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeleteProductDialog(product, true);
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

// Filters
const brands = page.props.brands || [];
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
    search: null,
    brand_id: null,
    brand: null,
});

const getQueryParams = () => {
    filters.search = route().params.search;
    filters.brand_id = parseInt(route().params.brand_id) || null;
    filters.brand =
        props.brands.find((brand) => brand.id === filters.brand_id) || null;
};
getQueryParams();

function getProducts() {
    let queryParams = {};

    if (filters.search) queryParams.search = filters.search;
    if (filters.brand_id) queryParams.brand_id = filters.brand_id;

    router.get(route("admin.product"), queryParams, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            products.value = props.products.data.map((product) => ({
                ...product,
                showDeleteModal: false,
            }));
        },
    });
}

onMounted(() => {
    if (page.props.flash.success) {
        openSuccessDialog(page.props.flash.success);
    }
});
</script>

<template>
    <AdminLayout title="Produk" :showTitle="true">
        <template #icon>
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="26"
                height="27"
                viewBox="0 0 26 27"
                class="fill-primary"
            >
                <rect opacity="0.01" y="0.695312" width="26" height="26" />
                <path
                    d="M12.1875 13.5111L3.25 9.75195V18.1261C3.25962 18.8391 3.68236 19.4817 4.33333 19.7728L12.1225 23.4453H12.1875V13.5111Z"
                />
                <path
                    d="M13 12.0701L22.2192 8.20256C22.064 8.03018 21.8762 7.89026 21.6667 7.79089L13.8667 4.14006C13.3181 3.8804 12.6819 3.8804 12.1333 4.14006L4.33332 7.79089C4.12376 7.89026 3.93598 8.03018 3.78082 8.20256L13 12.0701Z"
                />
                <path
                    d="M13.8125 13.5111V23.4453H13.8667L21.6667 19.7728C22.3141 19.4834 22.7362 18.846 22.75 18.137V9.75195L13.8125 13.5111Z"
                />
            </svg>
        </template>

        <template #trailing>
            <div class="flex items-center gap-2">
                <Dropdown
                    v-if="brands"
                    id="brand_id"
                    v-model="filters.brand_id"
                    :options="filteredBrands"
                    option-label="name"
                    option-value="id"
                    placeholder="Pilih Brand"
                    align="left"
                    required
                    :error="filters.errors.brand_id"
                    class="max-w-48 sm:hidden"
                    @update:modelValue="filters.errors.brand_id = null"
                    @onOpen="isBrandDropdownOpen = true"
                    @onClose="isBrandDropdownOpen = false"
                >
                    <template #trigger>
                        <TextInput
                            :modelValue="
                                filters.brand_id && !isBrandDropdownOpen
                                    ? filters.brand?.name
                                    : brandSearch
                            "
                            @update:modelValue="
                                filters.brand_id && !isBrandDropdownOpen
                                    ? null
                                    : (brandSearch = $event)
                            "
                            class="w-full"
                            textClass="text-sm sm:text-base"
                            :bgClass="!filters.brand_id ? 'bg-gray-100' : ''"
                            placeholder="Pilih Brand"
                        >
                            <template #suffix>
                                <button
                                    v-if="
                                        filters.brand_id && !isBrandDropdownOpen
                                    "
                                    type="button"
                                    class="absolute p-[7px] text-gray-400 bg-white rounded-full top-1 right-1 hover:bg-gray-100 transition-all duration-300 ease-in-out"
                                    @click="
                                        filters.brand_id = null;
                                        filters.brand = null;
                                        brandSearch = null;

                                        getProducts();
                                    "
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        class="size-4 sm:size-5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                                <button
                                    v-else
                                    type="button"
                                    class="absolute p-2 top-1.5 right-1"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        class="size-4 fill-gray-400"
                                    >
                                        <path
                                            d="M18.6054 7.3997C18.4811 7.273 18.3335 7.17248 18.1709 7.10389C18.0084 7.0353 17.8342 7 17.6583 7C17.4823 7 17.3081 7.0353 17.1456 7.10389C16.9831 7.17248 16.8355 7.273 16.7112 7.3997L11.4988 12.7028L6.28648 7.3997C6.03529 7.14415 5.69462 7.00058 5.33939 7.00058C4.98416 7.00058 4.64348 7.14415 4.3923 7.3997C4.14111 7.65526 4 8.00186 4 8.36327C4 8.72468 4.14111 9.07129 4.3923 9.32684L10.5585 15.6003C10.6827 15.727 10.8304 15.8275 10.9929 15.8961C11.1554 15.9647 11.3296 16 11.5055 16C11.6815 16 11.8557 15.9647 12.0182 15.8961C12.1807 15.8275 12.3284 15.727 12.4526 15.6003L18.6188 9.32684C19.1293 8.80747 19.1293 7.93274 18.6054 7.3997Z"
                                        />
                                    </svg>
                                </button>
                            </template>
                        </TextInput>
                    </template>
                    <template #content>
                        <ul class="overflow-y-auto max-h-60">
                            <li
                                v-for="brand in filteredBrands"
                                :key="brand.id"
                                @click="
                                    isBrandDropdownOpen = false;

                                    filters.brand_id = brand.id;
                                    filters.brand = brand;

                                    getProducts();
                                "
                                class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                            >
                                {{ brand.name }}
                            </li>
                        </ul>
                    </template>
                </Dropdown>
            </div>
        </template>

        <div>
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="bg-yellow-500 hover:bg-yellow-500/80 active:bg-yellow-500/90 focus:bg-yellow-500 focus:ring-yellow-500 max-sm:text-xs max-sm:px-4 max-sm:py-2 text-nowrap"
                    @click="$inertia.visit(route('admin.product.create'))"
                >
                    + Tambah Data
                </PrimaryButton>

                <div class="flex items-center gap-2">
                    <Dropdown
                        v-if="brands"
                        id="brand_id"
                        v-model="filters.brand_id"
                        :options="filteredBrands"
                        option-label="name"
                        option-value="id"
                        placeholder="Pilih Brand"
                        align="left"
                        required
                        :error="filters.errors.brand_id"
                        class="hidden max-w-48 sm:block"
                        @update:modelValue="filters.errors.brand_id = null"
                        @onOpen="isBrandDropdownOpen = true"
                        @onClose="isBrandDropdownOpen = false"
                    >
                        <template #trigger>
                            <TextInput
                                :modelValue="
                                    filters.brand_id && !isBrandDropdownOpen
                                        ? filters.brand?.name
                                        : brandSearch
                                "
                                @update:modelValue="
                                    filters.brand_id && !isBrandDropdownOpen
                                        ? null
                                        : (brandSearch = $event)
                                "
                                class="w-full"
                                textClass="text-sm sm:text-base"
                                :bgClass="
                                    !filters.brand_id ? 'bg-gray-100' : ''
                                "
                                placeholder="Pilih Brand"
                            >
                                <template #suffix>
                                    <button
                                        v-if="
                                            filters.brand_id &&
                                            !isBrandDropdownOpen
                                        "
                                        type="button"
                                        class="absolute p-[7px] text-gray-400 bg-white rounded-full top-1 right-1 hover:bg-gray-100 transition-all duration-300 ease-in-out"
                                        @click="
                                            filters.brand_id = null;
                                            filters.brand = null;
                                            brandSearch = null;

                                            getProducts();
                                        "
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            class="size-4 sm:size-5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        v-else
                                        type="button"
                                        class="absolute p-2 top-1.5 right-1"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            class="size-4 fill-gray-400"
                                        >
                                            <path
                                                d="M18.6054 7.3997C18.4811 7.273 18.3335 7.17248 18.1709 7.10389C18.0084 7.0353 17.8342 7 17.6583 7C17.4823 7 17.3081 7.0353 17.1456 7.10389C16.9831 7.17248 16.8355 7.273 16.7112 7.3997L11.4988 12.7028L6.28648 7.3997C6.03529 7.14415 5.69462 7.00058 5.33939 7.00058C4.98416 7.00058 4.64348 7.14415 4.3923 7.3997C4.14111 7.65526 4 8.00186 4 8.36327C4 8.72468 4.14111 9.07129 4.3923 9.32684L10.5585 15.6003C10.6827 15.727 10.8304 15.8275 10.9929 15.8961C11.1554 15.9647 11.3296 16 11.5055 16C11.6815 16 11.8557 15.9647 12.0182 15.8961C12.1807 15.8275 12.3284 15.727 12.4526 15.6003L18.6188 9.32684C19.1293 8.80747 19.1293 7.93274 18.6054 7.3997Z"
                                            />
                                        </svg>
                                    </button>
                                </template>
                            </TextInput>
                        </template>
                        <template #content>
                            <ul class="overflow-y-auto max-h-60">
                                <li
                                    v-for="brand in filteredBrands"
                                    :key="brand.id"
                                    @click="
                                        isBrandDropdownOpen = false;

                                        filters.brand_id = brand.id;
                                        filters.brand = brand;

                                        getProducts();
                                    "
                                    class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                                >
                                    {{ brand.name }}
                                </li>
                            </ul>
                        </template>
                    </Dropdown>
                    <TextInput
                        v-model="filters.search"
                        placeholder="Cari produk..."
                        bgClass="bg-gray-100"
                        textClass="text-sm sm:text-base"
                        @keyup.enter="getProducts()"
                    >
                        <template #suffix>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                class="absolute -translate-y-1/2 fill-gray-400 right-3 top-1/2 size-5"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M11 17C11.7879 17 12.5681 16.8448 13.2961 16.5433C14.0241 16.2417 14.6855 15.7998 15.2426 15.2426C15.7998 14.6855 16.2417 14.0241 16.5433 13.2961C16.8448 12.5681 17 11.7879 17 11C17 10.2121 16.8448 9.43185 16.5433 8.7039C16.2417 7.97595 15.7998 7.31451 15.2426 6.75736C14.6855 6.20021 14.0241 5.75825 13.2961 5.45672C12.5681 5.15519 11.7879 5 11 5C9.4087 5 7.88258 5.63214 6.75736 6.75736C5.63214 7.88258 5 9.4087 5 11C5 12.5913 5.63214 14.1174 6.75736 15.2426C7.88258 16.3679 9.4087 17 11 17ZM11 19C13.1217 19 15.1566 18.1571 16.6569 16.6569C18.1571 15.1566 19 13.1217 19 11C19 8.87827 18.1571 6.84344 16.6569 5.34315C15.1566 3.84285 13.1217 3 11 3C8.87827 3 6.84344 3.84285 5.34315 5.34315C3.84285 6.84344 3 8.87827 3 11C3 13.1217 3.84285 15.1566 5.34315 16.6569C6.84344 18.1571 8.87827 19 11 19Z"
                                />
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M15.3201 15.2903C15.5082 15.1035 15.7629 14.9991 16.0281 15C16.2933 15.0009 16.5472 15.1072 16.7341 15.2953L20.7091 19.2953C20.8908 19.4844 20.9909 19.7373 20.9879 19.9995C20.9849 20.2618 20.879 20.5123 20.6931 20.6972C20.5071 20.8822 20.256 20.9866 19.9937 20.9881C19.7315 20.9896 19.4791 20.8881 19.2911 20.7053L15.3161 16.7053C15.1291 16.5172 15.0245 16.2626 15.0253 15.9975C15.026 15.7323 15.1321 15.4783 15.3201 15.2913V15.2903Z"
                                />
                            </svg>
                        </template>
                    </TextInput>
                </div>
            </div>

            <!-- Table -->
            <div class="mt-4 overflow-x-auto rounded-t-lg sm:mt-6">
                <table class="table-default">
                    <thead>
                        <tr>
                            <th class="w-12">No</th>
                            <th class="min-w-[200px]">Nama Barang</th>
                            <th class="min-w-[150px] w-[200px]">Foto</th>
                            <th>Harga</th>
                            <th>Kategori Produk</th>
                            <th>Jumlah Produk</th>
                            <th class="w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(product, index) in products"
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
                            <td class="!whitespace-normal">
                                {{ product.name }}
                            </td>
                            <td>
                                <img
                                    v-if="product.images.length > 0"
                                    :src="getImageUrl(product.images[0].image)"
                                    :alt="product.name"
                                    class="object-cover h-[80px] sm:h-[120px] aspect-square rounded"
                                />
                                <div
                                    v-else
                                    class="flex items-center justify-center h-[80px] sm:h-[120px] bg-gray-100 rounded aspect-square"
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
                            <td class="text-center">
                                <p>
                                    {{
                                        (product.variants.length > 0
                                            ? Math.min(
                                                  ...product.variants.map(
                                                      (variant) =>
                                                          variant.final_selling_price
                                                  )
                                              )
                                            : 0
                                        ).toLocaleString("id-ID", {
                                            style: "currency",
                                            currency: "IDR",
                                            minimumFractionDigits: 0,
                                        })
                                    }}
                                    -
                                    {{
                                        (product.variants.length > 0
                                            ? Math.max(
                                                  ...product.variants.map(
                                                      (variant) =>
                                                          variant.final_selling_price
                                                  )
                                              )
                                            : 0
                                        ).toLocaleString("id-ID", {
                                            style: "currency",
                                            currency: "IDR",
                                            minimumFractionDigits: 0,
                                        })
                                    }}
                                </p>
                                <div
                                    v-if="product.discount > 0"
                                    class="flex items-center justify-center gap-2 mt-1"
                                >
                                    <p class="text-red-500 line-through">
                                        {{
                                            (product.variants.length > 0
                                                ? Math.min(
                                                      ...product.variants.map(
                                                          (variant) =>
                                                              variant.base_selling_price
                                                      )
                                                  )
                                                : 0
                                            ).toLocaleString("id-ID", {
                                                style: "currency",
                                                currency: "IDR",
                                                minimumFractionDigits: 0,
                                            })
                                        }}
                                        -
                                        {{
                                            (product.variants.length > 0
                                                ? Math.max(
                                                      ...product.variants.map(
                                                          (variant) =>
                                                              variant.base_selling_price
                                                      )
                                                  )
                                                : 0
                                            ).toLocaleString("id-ID", {
                                                style: "currency",
                                                currency: "IDR",
                                                minimumFractionDigits: 0,
                                            })
                                        }}
                                    </p>
                                    <DiscountTag
                                        v-if="product.discount > 0"
                                        :discount-type="product.discount_type"
                                        :discount="product.discount"
                                        class="!text-xs !px-1.5 !py-0.5"
                                    />
                                </div>
                            </td>
                            <td class="text-center">
                                <div
                                    class="flex flex-wrap gap-1.5 justify-center items-center"
                                >
                                    <div
                                        v-for="category in product.categories"
                                        :key="category.id"
                                        class="inline-block px-2 py-0.5 sm:px-2.5 sm:py-1 text-[10px] sm:text-xs text-blue-700 bg-blue-100 rounded-full"
                                        :class="{
                                            'bg-pink-100 text-pink-700':
                                                category.id === 2,
                                        }"
                                    >
                                        {{ category.name }}
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                {{
                                    product.variants.reduce(
                                        (total, variant) =>
                                            total + variant.current_stock_level,
                                        0
                                    )
                                }}
                            </td>
                            <td>
                                <AdminItemAction
                                    @edit="
                                        $inertia.visit(
                                            route('admin.product.edit', {
                                                product: product,
                                            })
                                        )
                                    "
                                    @delete="
                                        showDeleteProductDialog(product.id)
                                    "
                                />
                                <DeleteConfirmationDialog
                                    :show="product.showDeleteModal"
                                    :title="`Hapus Produk <b>${product.name}</b>?`"
                                    @close="closeDeleteProductDialog(product)"
                                    @delete="deleteProduct(product)"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>

                <SuccessDialog
                    :show="showSuccessDialog"
                    :title="successMessage"
                    @close="showSuccessDialog = false"
                />
            </div>

            <!-- Pagination -->
            <AdminPagination :links="props.products.links" />
        </div>
    </AdminLayout>
</template>

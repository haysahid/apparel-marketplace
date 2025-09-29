<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from "vue";
import { useForm } from "@inertiajs/vue3";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import TextInput from "@/Components/TextInput.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import MyProductCard from "@/Pages/MyStore/Product/MyProductCard.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import axios from "axios";
import SearchInput from "@/Components/SearchInput.vue";
import cookieManager from "@/plugins/cookie-manager";

const props = defineProps({
    isModal: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["selectProduct", "deselectProduct"]);

const data = ref({
    data: [],
    current_page: 1,
    per_page: 10,
    total: 0,
    links: [],
    from: 0,
    to: 0,
});
const products = computed(() => data.value.data || []);

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
const brands = ref([]);
const brandSearch = ref("");

const filteredBrands = computed(() => {
    return brands.value.filter((brand) =>
        brand.name
            .toLowerCase()
            .includes(brandSearch.value?.toLowerCase() || "")
    );
});

const filters = useForm({
    search: null,
    brand_id: null,
    brand: null,
    page: 1,
});

const getProductsStatus = ref(null);

function getProducts() {
    const queryParams = {
        page: filters.page || undefined,
        brand_id: filters.brand_id || undefined,
        search: filters.search || undefined,
    };

    getProductsStatus.value = "loading";
    axios
        .get("/api/my-store/product", {
            params: queryParams,
            headers: {
                Authorization: `Bearer ${cookieManager.getItem(
                    "access_token"
                )}`,
            },
        })
        .then((response) => {
            data.value = response.data.result;
            getProductsStatus.value = "success";
        })
        .catch((error) => {
            openErrorDialog("Gagal memuat produk.");
            getProductsStatus.value = "error";
        });
}
getProducts();

function getBrands() {
    axios
        .get("/api/my-store/brand-dropdown", {
            headers: {
                Authorization: `Bearer ${cookieManager.getItem(
                    "access_token"
                )}`,
            },
        })
        .then((response) => {
            brands.value = response.data.result;
        })
        .catch((error) => {
            openErrorDialog("Gagal memuat brand.");
        });
}
getBrands();

const changePage = (page) => {
    filters.page = page;
    getProducts();

    // Scroll product list component to top
    const productListElement = document.querySelector(
        props.isModal ? "product-list" : "#main-area"
    );
    if (productListElement) {
        productListElement.scrollTo({ top: 0, behavior: "smooth" });
    }
};

onMounted(() => {
    nextTick(() => {
        const input = document.getElementById(
            props.isModal ? "search-product-modal" : "search-product"
        ) as HTMLInputElement;
        input?.focus();
    });
});
</script>

<template>
    <DefaultCard
        :isMain="true"
        :class="{
            '!px-0 py-4 sm:py-6 flex flex-col gap-4 transition-all duration-300 ease-in-out':
                props.isModal,
        }"
    >
        <div
            class="flex items-center justify-between gap-4"
            :class="{
                'bg-white px-4 sm:px-6': props.isModal,
            }"
        >
            <h2 class="font-semibold text-nowrap">Pilih Produk</h2>
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
                            getProducts();
                        }
                    "
                    @search="brandSearch = $event"
                    @clear="
                        filters.brand_id = null;
                        filters.brand = null;
                        brandSearch = '';
                        getProducts();
                    "
                />
                <SearchInput
                    v-if="!props.isModal"
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

        <div v-if="props.isModal" class="w-full px-4 sm:px-6">
            <SearchInput
                id="search-product-modal"
                v-model="filters.search"
                placeholder="Cari produk..."
                @search="
                    filters.page = 1;
                    getProducts();
                "
            />
        </div>

        <!-- Mobile View -->
        <div
            id="product-list"
            class="flex flex-col gap-3 mt-4"
            :class="{
                'overflow-y-auto h-[calc(80vh-236px)] px-4 sm:px-6 !mt-0':
                    props.isModal,
            }"
        >
            <div
                v-if="getProductsStatus === 'loading'"
                class="flex flex-col w-full gap-2.5 mt-1.5"
            >
                <div
                    v-for="n in 3"
                    :key="n"
                    class="flex items-end gap-4 p-2.5 sm:p-4 bg-white border border-gray-200 rounded-xl animate-pulse"
                >
                    <div class="flex items-center justify-center w-full gap-4">
                        <div
                            class="bg-gray-100 rounded-lg size-[80px] sm:size-[100px]"
                        ></div>
                        <div class="flex flex-col flex-1 gap-2.5">
                            <div class="w-3/4 h-4 bg-gray-100 rounded-md"></div>
                            <div class="w-1/4 h-4 bg-gray-100 rounded-md"></div>
                            <div class="w-1/3 h-4 bg-gray-100 rounded-md"></div>
                        </div>
                    </div>
                    <div class="w-12 h-4 bg-gray-100 rounded-md"></div>
                </div>
            </div>
            <template v-else-if="products.length > 0">
                <div v-for="product in products" :key="product.id">
                    <MyProductCard
                        :product="product"
                        class="cursor-pointer"
                        @click="emit('selectProduct', product)"
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
        <div
            v-if="data.total > 0"
            class="flex flex-col gap-2 mt-4"
            :class="{
                'px-4 sm:px-6 mt-0': props.isModal,
            }"
        >
            <p class="text-xs text-gray-500 sm:text-sm text-start">
                Menampilkan {{ data.from }} - {{ data.to }} dari
                {{ data.total }} item
            </p>
            <DefaultPagination
                :isApi="true"
                :links="data.links"
                @change="changePage"
            />
        </div>

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
    </DefaultCard>
</template>

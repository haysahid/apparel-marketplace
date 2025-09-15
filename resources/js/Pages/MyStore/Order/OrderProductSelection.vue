<script setup lang="ts">
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import TextInput from "@/Components/TextInput.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import MyProductCard from "@/Pages/MyStore/Product/MyProductCard.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import axios from "axios";

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
});

const getProductsStatus = ref(null);

function getProducts() {
    let queryParams = {};

    if (filters.search) queryParams.search = filters.search;
    if (filters.brand_id) queryParams.brand_id = filters.brand_id;

    getProductsStatus.value = "loading";
    axios
        .get("/api/my-store/product", {
            params: queryParams,
            headers: {
                Authorization: "Bearer " + localStorage.getItem("access_token"),
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
                Authorization: "Bearer " + localStorage.getItem("access_token"),
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
</script>

<template>
    <DefaultCard :isMain="true">
        <div class="flex items-center justify-between gap-4">
            <!-- <PrimaryButton
                type="button"
                class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                @click="$inertia.visit(route('my-store.product.create'))"
            >
                Tambah
            </PrimaryButton> -->
            <h2 class="font-semibold">Pilih Produk</h2>
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
                                      (brand) => brand.id === brand.value
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
                <TextInput
                    v-model="filters.search"
                    placeholder="Cari produk..."
                    class="max-w-48"
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

        <!-- Mobile View -->
        <div
            class="flex flex-col gap-3 mt-4"
            :class="{ 'min-h-auto h-[68vh]': products.length == 0 }"
        >
            <div
                v-if="getProductsStatus === 'loading'"
                class="flex flex-col w-full gap-2.5 mt-1.5"
            >
                <div
                    v-for="n in 5"
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
            <div v-else class="flex items-center justify-center h-[40vh]">
                <p class="text-sm text-center text-gray-500">
                    Data tidak ditemukan.
                </p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="data.total > 0" class="flex flex-col gap-2 mt-4">
            <p class="text-xs text-gray-500 sm:text-sm">
                Menampilkan {{ data.from }} - {{ data.to }} dari
                {{ data.total }} item
            </p>
            <DefaultPagination :links="data.links" />
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

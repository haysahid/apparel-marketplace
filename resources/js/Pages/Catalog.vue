<script setup>
import LandingLayout from "@/Layouts/LandingLayout.vue";
import LandingSection from "@/Components/LandingSection.vue";
import ProductCard from "@/Components/ProductCard.vue";
import JoinUs from "@/Components/JoinUs.vue";
import CatalogFilter from "@/Components/CatalogFilter.vue";
import { ref, onMounted, watch, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import CatalogPagination from "@/Components/CatalogPagination.vue";

const page = usePage();
if (page.props.flash.access_token) {
    localStorage.setItem("access_token", page.props.flash.access_token);
}

const props = defineProps({
    products: null,
    filters: null,
});

const products = ref(
    props.products.data.map((product) => ({
        ...product,
        images: product.images.map((image) => ({
            ...image,
            image: image.image ? "/storage/" + image.image : null,
        })),
    }))
);

const catalogFilter = ref(null);

const filters = ref({
    search: null,
    brands: null,
    colors: null,
    categories: null,
    sizes: null,
});

const getFilters = computed(() => {
    return {
        brands:
            props.filters.brands.map((brand) => ({
                ...brand,
                selected: filters.value.brands?.includes(brand.name) || false,
            })) || [],
        colors:
            props.filters.colors.map((color) => ({
                ...color,
                selected: filters.value.colors?.includes(color.name) || false,
            })) || [],
        categories:
            props.filters.categories.map((category) => ({
                ...category,
                selected:
                    filters.value.categories?.includes(category.name) || false,
            })) || [],
        sizes:
            props.filters.sizes.map((size) => ({
                ...size,
                selected: filters.value.sizes?.includes(size.name) || false,
            })) || [],
    };
});

const getQueryParams = () => {
    filters.value.search = route().params.search || null;
    filters.value.brands = route().params.brands
        ? route().params.brands.split(",")
        : [];
    filters.value.colors = route().params.colors
        ? route().params.colors.split(",")
        : [];
    filters.value.categories = route().params.categories
        ? route().params.categories.split(",")
        : [];
    filters.value.sizes = route().params.sizes
        ? route().params.sizes.split(",")
        : [];
};
getQueryParams();

function onChangeCategories(categories) {
    const selectedCategories = categories
        .filter((category) => category.selected)
        .map((category) => category.name)
        .join(",");
    router.get(
        route("catalog"),
        {
            ...route().params,
            categories: selectedCategories || undefined,
            page: undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                products.value = page.props.products.data.map((product) => ({
                    ...product,
                    images: product.images.map((image) => ({
                        ...image,
                        image: image.image ? "/storage/" + image.image : null,
                    })),
                }));
            },
        }
    );
}

function onChangeSearch() {
    const searchQuery = filters.value.search;

    if (searchQuery && searchQuery.trim() === "") {
        filters.value.search = null;
    }

    router.get(
        route("catalog"),
        {
            ...route().params,
            search: searchQuery || undefined,
            page: undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                products.value = page.props.products.data.map((product) => ({
                    ...product,
                    images: product.images.map((image) => ({
                        ...image,
                        image: image.image ? "/storage/" + image.image : null,
                    })),
                }));
            },
        }
    );
}
</script>

<template>
    <LandingLayout title="Katalog">
        <!-- Search -->
        <LandingSection
            class="bg-secondary-box !min-h-[30vh] px-6 sm:px-12 md:px-[100px]"
        >
            <div
                class="flex flex-col items-center w-full py-12 text-center gap-9"
            >
                <div>
                    <h1 class="mb-4 text-3xl font-bold text-center">
                        Katalog Produk
                    </h1>
                    <p class="text-sm text-gray-700">
                        Silahkan cari produk disini.
                    </p>
                </div>
                <div class="w-full max-w-2xl mx-auto">
                    <form
                        @submit.prevent="onChangeSearch"
                        class="flex items-center space-x-4"
                    >
                        <label
                            for="search"
                            class="relative flex items-center w-full space-x-4"
                        >
                            <input
                                v-model="filters.search"
                                id="search"
                                type="text"
                                name="search"
                                placeholder="Cari produk..."
                                :autofocus="route().params.search"
                                class="w-full py-4 pl-8 pr-24 border border-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-light overflow-ellipsis focus:border-primary-light"
                            />
                            <button
                                type="submit"
                                class="absolute flex items-center justify-center px-4 py-2 text-white transition duration-200 rounded-lg bg-primary hover:bg-primary-dark right-3"
                            >
                                Cari
                            </button>
                        </label>
                    </form>
                </div>
            </div>
        </LandingSection>

        <!-- Content -->
        <div
            class="p-6 sm:p-12 md:p-[100px] md:pt-[80px] flex flex-col gap-12 lg:gap-20"
        >
            <LandingSection>
                <div
                    class="flex flex-col items-start justify-center w-full mx-auto md:flex-row gap-14 max-w-7xl"
                >
                    <!-- Filter -->
                    <CatalogFilter
                        ref="catalogFilter"
                        :filters="getFilters"
                        class="w-full md:w-1/3 lg:w-1/5"
                        @change="onChangeCategories"
                    />

                    <!-- Products -->
                    <div
                        data-aos="fade-up"
                        data-aos-duration="600"
                        class="flex flex-col items-start w-full gap-12 md:w-2/3 lg:w-4/5"
                    >
                        <div
                            v-if="products.length > 0"
                            class="grid w-full grid-cols-2 gap-6 lg:gap-8 lg:grid-cols-3"
                        >
                            <ProductCard
                                v-for="product in products || []"
                                :key="product.id"
                                :name="product.name"
                                :basePrice="product.lowest_base_selling_price"
                                :discount="product.discount"
                                :finalPrice="product.lowest_final_selling_price"
                                :image="product.images[0]?.image"
                                :description="product.brand?.name"
                                :slug="product.slug"
                            />
                        </div>
                        <div
                            v-else
                            class="flex items-center justify-center w-full h-64 text-gray-500"
                        >
                            <p>Tidak ada produk yang ditemukan.</p>
                        </div>

                        <!-- Pagination -->
                        <CatalogPagination
                            v-if="props.products.data.length > 0"
                            :links="props.products.links"
                        />
                    </div>
                </div>
            </LandingSection>

            <!-- Join Us -->
            <LandingSection id="join">
                <JoinUs />
            </LandingSection>
        </div>
    </LandingLayout>
</template>

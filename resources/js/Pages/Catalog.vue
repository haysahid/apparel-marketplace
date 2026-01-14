<script setup lang="ts">
import LandingLayout from "@/Layouts/LandingLayout.vue";
import LandingSection from "@/Components/LandingSection.vue";
import ProductCard from "@/Components/ProductCard.vue";
import JoinUs from "@/Components/JoinUs.vue";
import CatalogFilter from "@/Components/CatalogFilter.vue";
import { ref, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import CatalogPagination from "@/Components/CatalogPagination.vue";
import Chip from "@/Components/Chip.vue";
import ColorChip from "@/Components/ColorChip.vue";
import CustomPageProps from "@/types/model/CustomPageProps";

const page = usePage<CustomPageProps>();

const props = defineProps({
    products: {
        type: Object as () => PaginationModel<ProductEntity>,
        required: true,
    },
    filters: null,
});

const products = ref(props.products.data);

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

function onChangeBrands(brands) {
    const selectedBrands = brands
        .filter((brand) => brand.selected)
        .map((brand) => brand.name)
        .join(",");
    router.get(
        route("catalog"),
        {
            ...route().params,
            brands: selectedBrands || undefined,
            page: undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                products.value = page.props.products.data;
                getQueryParams();
            },
        }
    );
}

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
                products.value = page.props.products.data;
                getQueryParams();
            },
        }
    );
}

function onChangeColors(colors) {
    const selectedColors = colors
        .filter((color) => color.selected)
        .map((color) => color.name)
        .join(",");
    router.get(
        route("catalog"),
        {
            ...route().params,
            colors: selectedColors || undefined,
            page: undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                products.value = page.props.products.data;
                getQueryParams();
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
                products.value = page.props.products.data;
                getQueryParams();
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
                    <h1 class="mb-2 text-2xl font-bold text-center">
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
                                :autofocus="true"
                                class="w-full py-4 pl-8 pr-24 transition duration-150 ease-in-out border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-primary-light overflow-ellipsis focus:border-primary-light"
                            />
                            <button
                                type="submit"
                                class="absolute flex items-center justify-center px-4 py-2 text-white transition duration-200 rounded-lg bg-primary hover:bg-primary-hover right-3"
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
                    class="flex flex-col items-start justify-center w-full mx-auto md:flex-row gap-x-14 gap-y-6 max-w-7xl"
                >
                    <!-- Filter -->
                    <CatalogFilter
                        ref="catalogFilter"
                        :filters="getFilters"
                        class="w-full md:w-1/3 lg:w-1/5"
                        @changeBrands="onChangeBrands"
                        @changeCategories="onChangeCategories"
                        @changeColors="onChangeColors"
                    />

                    <div class="w-full md:w-2/3 lg:w-4/5">
                        <!-- Active Filters -->
                        <div
                            v-if="
                                catalogFilter?.selectedBrands?.length ||
                                catalogFilter?.selectedCategories?.length ||
                                catalogFilter?.selectedColors?.length
                            "
                            class="flex items-start justify-between w-full gap-4 mb-6"
                        >
                            <div class="flex flex-wrap items-center gap-2">
                                <Chip
                                    v-for="(
                                        brand, index
                                    ) in catalogFilter?.selectedBrands || []"
                                    :key="index"
                                    :label="brand.name"
                                    :selected="false"
                                    class="pr-2 !gap-1.5 !cursor-default text-sm"
                                >
                                    <template #prefix>
                                        <div
                                            class="flex items-center justify-center w-4 h-4 aspect-square me-1"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                class="size-4 fill-gray-300"
                                                :class="
                                                    brand.selected
                                                        ? 'fill-primary'
                                                        : 'fill-gray-300'
                                                "
                                            >
                                                <path
                                                    d="M5.5 7C5.10218 7 4.72064 6.84196 4.43934 6.56066C4.15804 6.27936 4 5.89782 4 5.5C4 5.10218 4.15804 4.72064 4.43934 4.43934C4.72064 4.15804 5.10218 4 5.5 4C5.89782 4 6.27936 4.15804 6.56066 4.43934C6.84196 4.72064 7 5.10218 7 5.5C7 5.89782 6.84196 6.27936 6.56066 6.56066C6.27936 6.84196 5.89782 7 5.5 7ZM21.41 11.58L12.41 2.58C12.05 2.22 11.55 2 11 2H4C2.89 2 2 2.89 2 4V11C2 11.55 2.22 12.05 2.59 12.41L11.58 21.41C11.95 21.77 12.45 22 13 22C13.55 22 14.05 21.77 14.41 21.41L21.41 14.41C21.78 14.05 22 13.55 22 13C22 12.44 21.77 11.94 21.41 11.58Z"
                                                />
                                            </svg>
                                        </div>
                                    </template>

                                    <template #suffix>
                                        <button
                                            type="button"
                                            class="text-gray-500 transition-all duration-300 ease-in-out rounded-full hover:text-red-700"
                                            @click="
                                                brand.selected = false;
                                                onChangeBrands(
                                                    catalogFilter.filters.brands
                                                );
                                                $event.stopPropagation();
                                            "
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                class="size-5"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </template>
                                </Chip>
                                <Chip
                                    v-for="(
                                        category, index
                                    ) in catalogFilter?.selectedCategories ||
                                    []"
                                    :key="index"
                                    :label="category.name"
                                    :selected="false"
                                    class="pr-2 !gap-1.5 !cursor-default text-sm"
                                >
                                    <template #prefix>
                                        <div
                                            class="flex items-center justify-center w-4 h-4 aspect-square me-1"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                class="size-4 fill-gray-300"
                                                :class="
                                                    category.selected
                                                        ? 'fill-primary'
                                                        : 'fill-gray-300'
                                                "
                                            >
                                                <path
                                                    d="M11.1501 3.40004L7.43012 9.48004C7.02012 10.14 7.50012 11 8.28012 11H15.7101C16.4901 11 16.9701 10.14 16.5601 9.48004L12.8501 3.40004C12.7617 3.25368 12.637 3.13262 12.4881 3.04859C12.3392 2.96456 12.1711 2.92041 12.0001 2.92041C11.8291 2.92041 11.661 2.96456 11.5121 3.04859C11.3632 3.13262 11.2385 3.25368 11.1501 3.40004Z"
                                                />
                                                <path
                                                    d="M17.5 22C19.9853 22 22 19.9853 22 17.5C22 15.0147 19.9853 13 17.5 13C15.0147 13 13 15.0147 13 17.5C13 19.9853 15.0147 22 17.5 22Z"
                                                />
                                                <path
                                                    d="M4 21.5H10C10.55 21.5 11 21.05 11 20.5V14.5C11 13.95 10.55 13.5 10 13.5H4C3.45 13.5 3 13.95 3 14.5V20.5C3 21.05 3.45 21.5 4 21.5Z"
                                                />
                                            </svg>
                                        </div>
                                    </template>

                                    <template #suffix>
                                        <button
                                            type="button"
                                            class="text-gray-500 transition-all duration-300 ease-in-out rounded-full hover:text-red-700"
                                            @click="
                                                category.selected = false;
                                                onChangeCategories(
                                                    catalogFilter.filters
                                                        .categories
                                                );
                                                $event.stopPropagation();
                                            "
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                class="size-5"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </template>
                                </Chip>
                                <ColorChip
                                    v-for="(
                                        color, index
                                    ) in catalogFilter?.selectedColors || []"
                                    :key="index"
                                    :label="color.name"
                                    :hexCode="color.hex_code"
                                    :selected="false"
                                    :fillRadio="true"
                                    class="pr-2 !gap-1.5 !cursor-default text-sm"
                                    radioClasses="me-1"
                                >
                                    <template #suffix>
                                        <button
                                            type="button"
                                            class="text-gray-500 transition-all duration-300 ease-in-out rounded-full hover:text-red-700"
                                            @click="
                                                color.selected = false;
                                                onChangeColors(
                                                    catalogFilter.filters.colors
                                                );
                                                $event.stopPropagation();
                                            "
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                class="size-5"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </template>
                                </ColorChip>
                            </div>
                            <button
                                type="button"
                                class="text-sm text-red-700 hover:underline w-fit text-start text-nowrap"
                                @click="
                                    catalogFilter.filters.categories.forEach(
                                        (category) => {
                                            category.selected = false;
                                        }
                                    );
                                    onChangeCategories(
                                        catalogFilter.filters.categories.map(
                                            (category) => ({
                                                ...category,
                                                selected: false,
                                            })
                                        )
                                    );
                                "
                            >
                                Hapus Filter
                            </button>
                        </div>

                        <!-- Products -->
                        <div class="flex flex-col items-start w-full gap-12">
                            <div
                                v-if="products.length > 0"
                                class="grid w-full grid-cols-2 gap-6 lg:gap-8 lg:grid-cols-3"
                            >
                                <template
                                    v-for="(product, index) in products"
                                    :key="product.id"
                                >
                                    <Transition
                                        name="fade-up"
                                        mode="out-in"
                                        appear
                                        @before-enter="
                                                (el: HTMLElement) => {
                                                    el.style.transitionDelay =
                                                        index % 10 * 100 + 'ms';
                                                }
                                            "
                                        @after-enter="
                                                (el: HTMLElement) => {
                                                    el.style.transitionDelay = '';
                                                }
                                            "
                                        @after-leave="
                                                (el: HTMLElement) => {
                                                    el.style.transitionDelay = '';
                                                }
                                            "
                                    >
                                        <ProductCard
                                            :name="product.name"
                                            :basePrice="
                                                product.lowest_base_selling_price
                                            "
                                            :discount="product.discount"
                                            :finalPrice="
                                                product.lowest_final_selling_price
                                            "
                                            :image="(product.images[0]?.image as string | null)"
                                            :description="product.brand?.name"
                                            :slug="product.slug"
                                        />
                                    </Transition>
                                </template>
                            </div>
                            <div
                                v-else
                                class="flex items-center justify-center w-full h-64 text-gray-500"
                            >
                                <p>Tidak ada produk yang ditemukan.</p>
                            </div>

                            <!-- Pagination -->
                            <div
                                v-if="props.products.total > 0"
                                class="flex flex-col gap-4 mt-4"
                            >
                                <p class="text-xs text-gray-500 sm:text-sm">
                                    Menampilkan {{ props.products.from }} -
                                    {{ props.products.to }} dari
                                    {{ props.products.total }} item
                                </p>
                                <CatalogPagination
                                    :links="props.products.links"
                                />
                            </div>
                        </div>
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

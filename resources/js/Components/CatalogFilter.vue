<script setup>
import { ref, computed } from "vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputLabel from "@/Components/InputLabel.vue";
import ColorChip from "./ColorChip.vue";
import CategoryFilterModal from "@/Pages/Catalog/CategoryFilterModal.vue";
import BrandFilterModal from "@/Pages/Catalog/BrandFilterModal.vue";
import ColorFilterModal from "@/Pages/Catalog/ColorFilterModal.vue";

const props = defineProps({
    filters: Object,
});

const filters = ref(props.filters);
const showingFilterDropdown = ref(false);

// Brands
const showAllBrands = false;
const showBrandsModal = ref(false);
const brandSearch = ref("");
const brands = computed(() => {
    if (showAllBrands) {
        return filters.value.brands;
    }

    return filters.value.brands.slice(0, 5);
});
const filteredBrands = computed(() => {
    if (!brandSearch.value) {
        return props.filters.brands;
    }

    return props.filters.brands.filter((brand) =>
        brand.name.toLowerCase().includes(brandSearch.value.toLowerCase())
    );
});

// Category
const showAllCategories = false;
const showCategoriesModal = ref(false);
const categories = computed(() => {
    if (showAllCategories) {
        return filters.value.categories;
    }

    return filters.value.categories.slice(0, 5);
});

// Color
const showAllColors = false;
const showColorsModal = ref(false);
const colorSearch = ref("");
const colors = computed(() => {
    if (showAllColors) {
        return filters.value.colors;
    }

    return filters.value.colors.slice(0, 5);
});
const filteredColors = computed(() => {
    if (!colorSearch.value) {
        return props.filters.colors;
    }

    return props.filters.colors.filter((color) =>
        color.name.toLowerCase().includes(colorSearch.value.toLowerCase())
    );
});

const emit = defineEmits(["changeBrands", "changeCategories", "changeColors"]);

defineExpose({
    filters,
    selectedBrands: computed(() => {
        return filters.value.brands.filter((brand) => brand.selected);
    }),
    selectedCategories: computed(() => {
        return filters.value.categories.filter((category) => category.selected);
    }),
    selectedColors: computed(() => {
        return filters.value.colors.filter((color) => color.selected);
    }),
});
</script>

<template>
    <div>
        <!-- Primary Filter -->
        <div class="hidden md:block">
            <div class="flex items-center justify-between gap-4 py-1 mb-4">
                <h2 class="text-lg font-bold">Filter</h2>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-6 h-6 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16m-7 6h7"
                    />
                </svg>
            </div>

            <div class="flex-col hidden gap-6 md:flex">
                <!-- Brand -->
                <div>
                    <h3 class="mb-4 text-base font-semibold">Brand</h3>
                    <div v-if="filters?.brands" class="flex flex-wrap gap-2">
                        <div
                            v-for="brand in brands || []"
                            :key="brand.id"
                            class="flex items-center justify-start w-full gap-4 p-2 transition-all duration-200 ease-in-out border border-gray-200 rounded-lg cursor-pointer hover:border-gray-400 min-h-14"
                            :class="{
                                'border-primary bg-primary/5 outline outline-2 outline-primary -outline-offset-2':
                                    brand.selected,
                            }"
                            @click="
                                brand.selected
                                    ? (brand.selected = false)
                                    : (brand.selected = true);

                                emit('changeBrands', filters.brands);
                            "
                        >
                            <img
                                v-if="brand.logo"
                                :src="`/storage/${brand.logo}`"
                                class="object-contain w-8 aspect-square"
                            />
                            <div
                                v-else
                                class="flex items-center justify-center w-8 aspect-square"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    class="size-8 fill-gray-300"
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
                            <div
                                class="flex items-center justify-between w-full gap-2"
                            >
                                <p class="text-sm font-medium text-gray-700">
                                    {{ brand.name }}
                                </p>
                                <div
                                    class="p-1 text-xs font-medium text-center text-gray-500 rounded-md bg-gray-50 min-w-6"
                                >
                                    {{ brand.products_count }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="mt-4 text-sm text-primary hover:underline w-fit"
                        @click="showBrandsModal = true"
                    >
                        Lainnya
                    </button>
                </div>

                <!-- Category -->
                <div>
                    <h3 class="mb-4 text-base font-semibold">Kategori</h3>
                    <div v-if="filters?.categories" class="flex flex-col gap-4">
                        <div
                            v-for="category in categories || []"
                            :key="category.id"
                            class="flex items-center justify-start"
                        >
                            <label
                                :for="`category-${category.id}`"
                                class="flex items-center gap-2 cursor-pointer [&>*]:cursor-pointer justify-start"
                            >
                                <Checkbox
                                    :id="`category-${category.id}`"
                                    :label="category.name"
                                    :checked="category.selected"
                                    @update:checked="
                                        category.selected
                                            ? (category.selected = false)
                                            : (category.selected = true);

                                        emit(
                                            'changeCategories',
                                            filters.categories
                                        );
                                    "
                                />
                                <InputLabel
                                    :for="`category-${category.id}`"
                                    :value="category.name"
                                    class="text-sm text-gray-500"
                                />
                            </label>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="mt-4 text-sm text-primary hover:underline w-fit"
                        @click="showCategoriesModal = true"
                    >
                        Lainnya
                    </button>
                </div>

                <!-- Color -->
                <div>
                    <h3 class="mb-4 text-base font-semibold">Warna</h3>
                    <div v-if="filters?.colors" class="grid grid-cols-2 gap-2">
                        <ColorChip
                            v-for="color in colors || []"
                            :key="color.id"
                            :label="color.name"
                            :hexCode="color.hex_code"
                            :selected="color.selected"
                            class="!justify-start"
                            @click="
                                color.selected
                                    ? (color.selected = false)
                                    : (color.selected = true);

                                emit('changeColors', filters.colors);
                            "
                        >
                        </ColorChip>
                    </div>
                    <button
                        type="button"
                        class="mt-4 text-sm text-primary hover:underline w-fit"
                        @click="showColorsModal = true"
                    >
                        Lainnya
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter Dropdown -->
        <div
            class="w-full bg-white rounded-lg shadow-sm md:hidden outline outline-1 outline-gray-200 group"
            :class="{
                'hover:outline hover:outline-primary-light':
                    !showingFilterDropdown,
            }"
        >
            <button
                class="flex items-center justify-between w-full gap-4 p-4 text-gray-500 transition-all duration-300 rounded-lg hover:bg-primary/5 hover:text-gray-700 focus:outline-none"
                @click="showingFilterDropdown = !showingFilterDropdown"
            >
                <h2 class="text-gray-700">Filter</h2>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-6 h-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16m-7 6h7"
                    />
                </svg>
            </button>

            <div
                class="px-4 pt-2.5 pb-4 flex flex-col gap-6"
                :class="{ hidden: !showingFilterDropdown }"
            >
                <!-- Brand -->
                <div>
                    <h3 class="mb-4 text-base font-semibold">Brand</h3>
                    <div v-if="filters?.brands" class="flex flex-wrap gap-2">
                        <div
                            v-for="brand in brands || []"
                            :key="brand.id"
                            class="flex items-center justify-start w-full gap-4 p-2 transition-all duration-200 ease-in-out border border-gray-200 rounded-lg cursor-pointer hover:border-gray-400 min-h-14"
                            :class="{
                                'border-primary bg-primary/5 outline outline-2 outline-primary -outline-offset-2':
                                    brand.selected,
                            }"
                            @click="
                                brand.selected
                                    ? (brand.selected = false)
                                    : (brand.selected = true);

                                emit('changeBrands', filters.brands);
                            "
                        >
                            <img
                                v-if="brand.logo"
                                :src="`/storage/${brand.logo}`"
                                class="object-contain w-8 aspect-square"
                            />
                            <div
                                v-else
                                class="flex items-center justify-center w-8 aspect-square"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    class="size-8 fill-gray-300"
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
                            <div
                                class="flex items-center justify-between w-full gap-2"
                            >
                                <p class="text-sm font-medium text-gray-700">
                                    {{ brand.name }}
                                </p>
                                <div
                                    class="p-1 text-xs font-medium text-center text-gray-500 rounded-md bg-gray-50 min-w-6"
                                >
                                    {{ brand.products_count }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="mt-4 text-sm text-primary hover:underline w-fit"
                        @click="showBrandsModal = true"
                    >
                        Lainnya
                    </button>
                </div>

                <!-- Category -->
                <div>
                    <h3 class="mb-4 text-base font-semibold">Kategori</h3>
                    <div v-if="filters?.categories" class="flex flex-col gap-4">
                        <div
                            v-for="category in categories || []"
                            :key="category.id"
                            class="flex items-center justify-start"
                        >
                            <label
                                :for="`category-${category.id}`"
                                class="flex items-center gap-2 cursor-pointer [&>*]:cursor-pointer justify-start"
                            >
                                <Checkbox
                                    :id="`category-${category.id}`"
                                    :label="category.name"
                                    :checked="category.selected"
                                    @update:checked="
                                        category.selected
                                            ? (category.selected = false)
                                            : (category.selected = true);

                                        emit(
                                            'changeCategories',
                                            filters.categories
                                        );
                                    "
                                />
                                <InputLabel
                                    :for="`category-${category.id}`"
                                    :value="category.name"
                                    class="text-sm text-gray-500"
                                />
                            </label>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="mt-4 text-sm text-primary hover:underline w-fit"
                        @click="showCategoriesModal = true"
                    >
                        Lainnya
                    </button>
                </div>

                <!-- Color -->
                <div>
                    <h3 class="mb-4 text-base font-semibold">Warna</h3>
                    <div v-if="filters?.colors" class="grid grid-cols-2 gap-2">
                        <ColorChip
                            v-for="color in colors || []"
                            :key="color.id"
                            :label="color.name"
                            :hexCode="color.hex_code"
                            :selected="color.selected"
                            class="!justify-start"
                            @click="
                                color.selected
                                    ? (color.selected = false)
                                    : (color.selected = true);

                                emit('changeColors', filters.colors);
                            "
                        >
                        </ColorChip>
                    </div>
                    <button
                        type="button"
                        class="mt-4 text-sm text-primary hover:underline w-fit"
                        @click="showColorsModal = true"
                    >
                        Lainnya
                    </button>
                </div>
            </div>
        </div>

        <BrandFilterModal
            :show="showBrandsModal"
            :brands="filters.brands"
            @close="showBrandsModal = false"
            @change="emit('changeBrands', filters.brands)"
        />

        <CategoryFilterModal
            :show="showCategoriesModal"
            :categories="filters.categories"
            @close="showCategoriesModal = false"
            @change="emit('changeCategories', filters.categories)"
        />

        <ColorFilterModal
            :show="showColorsModal"
            :colors="filters.colors"
            @close="showColorsModal = false"
            @change="emit('changeColors', filters.colors)"
        />
    </div>
</template>

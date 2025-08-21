<script setup>
import { ref } from "vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputLabel from "@/Components/InputLabel.vue";

const props = defineProps({
    filters: Object,
});

const filters = ref(props.filters);

const showingFilterDropdown = ref(false);

const emit = defineEmits(["change"]);

defineExpose({
    filters,
});
</script>

<template>
    <div>
        <!-- Primary Filter -->
        <div class="hidden md:block">
            <h2 class="mb-4 text-xl font-bold">Kategori</h2>
            <div
                v-if="filters?.categories"
                class="hidden gap-4 md:flex md:flex-col"
            >
                <div
                    v-for="category in filters.categories || []"
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

                                emit('change', filters.categories);
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
        </div>

        <!-- Filter Dropdown -->
        <div
            data-aos="fade-up"
            data-aos-duration="300"
            class="w-full bg-white rounded-lg shadow-md md:hidden outline outline-1 outline-gray-100"
        >
            <button
                class="flex items-center justify-between w-full gap-4 p-4 text-gray-500 hover:text-gray-700 focus:outline-none"
                @click="showingFilterDropdown = !showingFilterDropdown"
            >
                <h2>Filter</h2>
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
                class="px-4 pt-2.5 pb-4"
                :class="{ hidden: !showingFilterDropdown }"
            >
                <h2 class="mb-4 text-xl font-bold">Kategori</h2>
                <div class="grid grid-cols-2 gap-2">
                    <label
                        v-for="category in filters.categories || []"
                        :key="category.id"
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

                                emit('change', filters.categories);
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
        </div>
    </div>
</template>

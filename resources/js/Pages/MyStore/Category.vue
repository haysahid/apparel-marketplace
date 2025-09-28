<script setup lang="ts">
import { ref, onMounted, nextTick, computed } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import { router } from "@inertiajs/vue3";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultTable from "@/Components/DefaultTable.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import { useScreenSize } from "@/plugins/screen-size";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import AdminItemCard from "@/Components/AdminItemCard.vue";
import InfoTooltip from "@/Components/InfoTooltip.vue";
import { getImageUrl, scrollToTop } from "@/plugins/helpers";
import CustomPageProps from "@/types/model/CustomPageProps";
import SearchInput from "@/Components/SearchInput.vue";

const screenSize = useScreenSize();

const props = defineProps({
    categories: Object as () => PaginationModel<CategoryEntity>,
});

const categories = ref<PaginationModel<CategoryEntity>>({
    ...props.categories,
    data: props.categories.data.map((category) => ({
        ...category,
        showDeleteModal: false,
    })),
});

const filters = useForm({
    page: null,
    search: null,
});

const getQueryParams = () => {
    filters.page = route().params.page || null;
    filters.search = route().params.search || null;
};
getQueryParams();

const queryParams = computed(() => {
    return {
        page: filters.page || undefined,
        search: filters.search || undefined,
    };
});

function getCategories() {
    router.get(route("my-store.category"), queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            categories.value = {
                ...props.categories,
                data: props.categories.data.map((category) => ({
                    ...category,
                    showDeleteModal: false,
                })),
            };
            scrollToTop({ id: "main-area" });
            setSearchFocus();
        },
    });
}

const selectedCategory = ref(null);
const showDeleteCategoryDialog = ref(false);

const openDeleteCategoryDialog = (category) => {
    if (category) {
        selectedCategory.value = category;
        showDeleteCategoryDialog.value = true;
    }
};

const closeDeleteCategoryDialog = (result = false) => {
    showDeleteCategoryDialog.value = false;
    if (result) {
        selectedCategory.value = null;
        openSuccessDialog("Data Berhasil Dihapus");
    }
};

const deleteCategory = () => {
    if (selectedCategory.value) {
        const form = useForm({});
        form.delete(
            route("my-store.category.destroy", {
                category: selectedCategory.value,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeleteCategoryDialog(true);
                    getCategories();
                },
            }
        );
    }
};

const showSuccessDialog = ref(false);
const successMessage = ref("Berhasil");

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

const page = usePage<CustomPageProps>();

function canEdit(category) {
    return (
        page.props.auth.is_admin ||
        page.props.auth.user.stores.some(
            (store) => store.id === category.store_id
        )
    );
}

function setSearchFocus() {
    nextTick(() => {
        const input = document.getElementById(
            "search-category"
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
    <MyStoreLayout title="Kategori" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="$inertia.visit(route('my-store.category.create'))"
                >
                    Tambah
                </PrimaryButton>
                <SearchInput
                    id="search-category"
                    v-model="filters.search"
                    placeholder="Cari kategori..."
                    class="max-w-48"
                    @search="
                        filters.page = 1;
                        getCategories();
                    "
                />
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="categories.data.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th class="w-24">Gambar</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Produk</th>
                        <th class="w-24">Aksi</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr
                        v-for="(category, index) in categories.data"
                        :key="category.id"
                    >
                        <td>
                            {{
                                index +
                                1 +
                                (props.categories.current_page - 1) *
                                    props.categories.per_page
                            }}
                        </td>
                        <td>
                            <img
                                v-if="category.image"
                                :src="getImageUrl(category.image)"
                                alt="Logo Brand"
                                class="object-contain h-[40px] rounded aspect-[3/2]"
                            />
                            <div
                                v-else
                                class="flex items-center justify-center h-[40px] bg-gray-100 rounded aspect-[3/2]"
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
                        <td>
                            {{ category.name }}
                        </td>
                        <td>
                            {{ category.total_products }}
                        </td>
                        <td>
                            <div class="flex items-center justify-start gap-2">
                                <AdminItemAction
                                    v-if="canEdit(category)"
                                    @edit="
                                        $inertia.visit(
                                            route('my-store.category.edit', {
                                                category: category,
                                            })
                                        )
                                    "
                                    @delete="openDeleteCategoryDialog(category)"
                                />
                                <InfoTooltip
                                    v-if="!canEdit(category)"
                                    :id="`table-tooltip-hint-${category.id}`"
                                    text="Kategori bawaan sistem"
                                />
                            </div>
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <div
                    v-if="categories.data.length > 0"
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                >
                    <AdminItemCard
                        v-for="(category, index) in categories.data"
                        :key="category.id"
                        :name="category.name"
                        :description="`${category.total_products} Produk`"
                        :image="category.image"
                        :hideActions="!canEdit(category)"
                        disabledHint="Kategori bawaan sistem"
                        imageClass="!w-[60px]"
                        @edit="
                            $inertia.visit(
                                route('my-store.category.edit', {
                                    category: category,
                                })
                            )
                        "
                        @delete="openDeleteCategoryDialog(category)"
                    />
                </div>
                <div v-else class="flex items-center justify-center h-[40vh]">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="categories.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ categories.from }} - {{ categories.to }} dari
                    {{ categories.total }} item
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="categories.links"
                    @change="
                        (page) => {
                            filters.page = page;
                            getCategories();
                        }
                    "
                />
            </div>
        </DefaultCard>

        <DeleteConfirmationDialog
            :show="showDeleteCategoryDialog"
            :title="`Hapus Kategori <b>${selectedCategory?.name}</b>?`"
            @close="closeDeleteCategoryDialog()"
            @delete="deleteCategory()"
        />

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

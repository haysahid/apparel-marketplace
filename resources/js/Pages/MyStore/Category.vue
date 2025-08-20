<script setup>
import { ref, onMounted } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import TextInput from "@/Components/TextInput.vue";
import { router } from "@inertiajs/vue3";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultTable from "@/Components/DefaultTable.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import { useScreenSize } from "@/plugins/screen-size";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import MyStoreBrandCard from "./Brand/MyStoreBrandCard.vue";
import AdminItemCard from "@/Components/AdminItemCard.vue";
import InfoTooltip from "@/Components/InfoTooltip.vue";

const screenSize = useScreenSize();

const props = defineProps({
    categories: null,
});

const categories = ref(
    props.categories.data.map((category) => ({
        ...category,
        image: category.image ? "/storage/" + category.image : null,
        showDeleteModal: false,
    }))
);

const filters = useForm({
    search: null,
});

const getQueryParams = () => {
    filters.search = route().params.search;
};
getQueryParams();

function getCategories() {
    let queryParams = {};

    if (filters.search) queryParams.search = filters.search;

    router.get(route("my-store.category"), queryParams, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            categories.value = props.categories.data.map((category) => ({
                ...category,
                image: category.image ? "/storage/" + category.image : null,
                showDeleteModal: false,
            }));
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

const closeDeleteCategoryDialog = (result) => {
    showDeleteCategoryDialog.value = false;
    if (result) {
        selectedCategory.value = null;
        openSuccessDialog("Data Berhasil Dihapus");
        categories.value = categories.value.filter(
            (b) => b.id !== selectedCategory.value?.id
        );
    }
};

const deleteCategory = () => {
    if (selectedCategory.value) {
        const form = useForm();
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

const page = usePage();

function canEdit(category) {
    return (
        page.props.auth.user.is_admin ||
        page.props.auth.user.stores.some(
            (store) => store.id === category.store_id
        )
    );
}

onMounted(() => {
    if (page.props.flash.success) {
        openSuccessDialog(page.props.flash.success);
    }
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
                <TextInput
                    v-model="filters.search"
                    placeholder="Cari kategori..."
                    class="max-w-48"
                    @keyup.enter="getCategories()"
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

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="categories.length === 0"
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
                        v-for="(category, index) in categories"
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
                                :src="category.image"
                                alt="Logo Brand"
                                class="object-contain h-[60px] rounded aspect-[3/2]"
                            />
                            <div
                                v-else
                                class="flex items-center justify-center h-[60px] bg-gray-100 rounded aspect-[3/2]"
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
                    v-if="categories.length > 0"
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                >
                    <AdminItemCard
                        v-for="(category, index) in categories"
                        :key="category.id"
                        :name="category.name"
                        :description="`${category.total_products} Produk`"
                        :image="category.image"
                        :hideActions="!canEdit(category)"
                        disabledHint="Kategori bawaan sistem"
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
            <div
                v-if="props.categories.total > 0"
                class="flex flex-col gap-2 mt-4"
            >
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ props.categories.from }} -
                    {{ props.categories.to }} dari
                    {{ props.categories.total }} item
                </p>
                <DefaultPagination :links="props.categories.links" />
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

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
import CustomPageProps from "@/types/model/CustomPageProps";
import { scrollToTop } from "@/plugins/helpers";
import SearchInput from "@/Components/SearchInput.vue";

const screenSize = useScreenSize();

const props = defineProps({
    colors: Object as () => PaginationModel<ColorEntity>,
});

const colors = ref<PaginationModel<ColorEntity>>({
    ...props.colors,
    data: props.colors.data.map((color) => ({
        ...color,
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

function getColors() {
    router.get(route("my-store.color"), queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            colors.value = {
                ...props.colors,
                data: props.colors.data.map((color) => ({
                    ...color,
                    showDeleteModal: false,
                })),
            };
            scrollToTop({ id: "main-area" });
            setSearchFocus();
        },
    });
}

const selectedColor = ref(null);
const showDeleteColorDialog = ref(false);

const openDeleteColorDialog = (color) => {
    if (color) {
        selectedColor.value = color;
        showDeleteColorDialog.value = true;
    }
};

const closeDeleteColorDialog = (result = false) => {
    showDeleteColorDialog.value = false;
    if (result) {
        selectedColor.value = null;
        openSuccessDialog("Data Berhasil Dihapus");
    }
};

const deleteColor = () => {
    if (selectedColor.value) {
        const form = useForm({});
        form.delete(
            route("my-store.color.destroy", {
                color: selectedColor.value,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeleteColorDialog(true);
                    getColors();
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

function canEdit(color) {
    return (
        page.props.auth.is_admin ||
        page.props.auth.user.stores.some((store) => store.id === color.store_id)
    );
}

function setSearchFocus() {
    nextTick(() => {
        const input = document.getElementById(
            "search-color"
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
    <MyStoreLayout title="Warna" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="$inertia.visit(route('my-store.color.create'))"
                >
                    Tambah
                </PrimaryButton>
                <SearchInput
                    id="search-color"
                    v-model="filters.search"
                    placeholder="Cari warna..."
                    class="max-w-48"
                    @search="
                        filters.page = 1;
                        getColors();
                    "
                />
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="colors.data.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Warna</th>
                        <th>Kode Warna</th>
                        <th class="w-24">Aksi</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr v-for="(color, index) in colors.data" :key="color.id">
                        <td>
                            {{
                                index +
                                1 +
                                (props.colors.current_page - 1) *
                                    props.colors.per_page
                            }}
                        </td>
                        <td>
                            {{ color.name }}
                        </td>
                        <td>
                            <div
                                v-if="color.hex_code"
                                class="flex items-center gap-2"
                            >
                                <span
                                    class="inline-block w-4 h-4 rounded-full"
                                    :style="{
                                        backgroundColor: color.hex_code,
                                    }"
                                ></span>
                                <p>{{ color.hex_code }}</p>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center justify-start gap-2">
                                <AdminItemAction
                                    v-if="canEdit(color)"
                                    @edit="
                                        $inertia.visit(
                                            route('my-store.color.edit', {
                                                color: color,
                                            })
                                        )
                                    "
                                    @delete="openDeleteColorDialog(color)"
                                />
                                <InfoTooltip
                                    v-if="!canEdit(color)"
                                    :id="`table-tooltip-hint-${color.id}`"
                                    text="Warna bawaan sistem"
                                />
                            </div>
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <div
                    v-if="colors.data.length > 0"
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <AdminItemCard
                        v-for="(color, index) in colors.data"
                        :key="color.id"
                        :name="color.name"
                        :showImage="false"
                        :description="color.hex_code"
                        :hideActions="!canEdit(color)"
                        disabledHint="Warna bawaan sistem"
                        @edit="
                            $inertia.visit(
                                route('my-store.color.edit', {
                                    color: color,
                                })
                            )
                        "
                        @delete="openDeleteColorDialog(color)"
                    >
                        <template #leading>
                            <div
                                class="inline-block rounded-full size-6 aspect-square"
                                :style="{
                                    backgroundColor: color.hex_code,
                                }"
                            ></div>
                        </template>
                    </AdminItemCard>
                </div>
                <div v-else class="flex items-center justify-center py-10">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="colors.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ colors.from }} - {{ colors.to }} dari
                    {{ colors.total }} item
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="colors.links"
                    @change="
                        (page) => {
                            filters.page = page;
                            getColors();
                        }
                    "
                />
            </div>
        </DefaultCard>

        <DeleteConfirmationDialog
            :show="showDeleteColorDialog"
            :title="`Hapus Kategori <b>${selectedColor?.name}</b>?`"
            @close="closeDeleteColorDialog()"
            @delete="deleteColor()"
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

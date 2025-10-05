<script setup lang="ts">
import { ref, onMounted, nextTick, computed } from "vue";
import { usePage, useForm, Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import { router } from "@inertiajs/vue3";
import DefaultTable from "@/Components/DefaultTable.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import { useScreenSize } from "@/plugins/screen-size";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import InfoTooltip from "@/Components/InfoTooltip.vue";
import { getImageUrl } from "@/plugins/helpers.js";
import CustomPageProps from "@/types/model/CustomPageProps";
import StoreCard from "./Store/StoreCard.vue";
import { scrollToTop } from "@/plugins/helpers";
import SearchInput from "@/Components/SearchInput.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const screenSize = useScreenSize();

const props = defineProps({
    stores: {
        type: Object as () => PaginationModel<StoreEntity>,
        default: null,
    },
});

const stores = ref<PaginationModel<StoreEntity>>({
    ...props.stores,
    data: props.stores.data.map((store) => ({
        ...store,
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

function getPartners() {
    router.get(route("admin.store"), queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            stores.value = {
                ...props.stores,
                data: props.stores.data.map((store) => ({
                    ...store,
                    showDeleteModal: false,
                })),
            };
            scrollToTop({ id: "main-area" });
            setSearchFocus();
        },
    });
}

const selectedPartner = ref(null);
const showDeletePartnerDialog = ref(false);

const openDeletePartnerDialog = (store) => {
    console.log("openDeletePartnerDialog", store);
    if (store) {
        selectedPartner.value = store;
        showDeletePartnerDialog.value = true;
    }
};

const closeDeletePartnerDialog = (result = false) => {
    showDeletePartnerDialog.value = false;
    if (result) {
        selectedPartner.value = null;
        openSuccessDialog("Data Berhasil Dihapus");
    }
};

const deletePartner = () => {
    if (selectedPartner.value) {
        const form = useForm({});
        form.delete(
            route("admin.store.destroy", {
                store: selectedPartner.value,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeletePartnerDialog(true);
                    getPartners();
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

function canEdit(store) {
    return (
        page.props.auth.is_admin ||
        page.props.auth.user.stores.some((store) => store.id === store.store_id)
    );
}

function setSearchFocus() {
    nextTick(() => {
        const input = document.getElementById(
            "search-store"
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
    <AdminLayout title="Toko" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="$inertia.visit(route('admin.store.create'))"
                >
                    Tambah
                </PrimaryButton>
                <SearchInput
                    id="search-store"
                    v-model="filters.search"
                    placeholder="Cari toko..."
                    class="max-w-48"
                    @search="
                        filters.page = 1;
                        getPartners();
                    "
                />
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="stores.data.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th class="w-24">Logo</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No. HP</th>
                        <th>Alamat</th>
                        <th class="w-24">Aksi</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr v-for="(store, index) in stores.data" :key="store.id">
                        <td>
                            {{
                                index +
                                1 +
                                (props.stores.current_page - 1) *
                                    props.stores.per_page
                            }}
                        </td>
                        <td>
                            <img
                                v-if="store.logo"
                                :src="getImageUrl(store.logo)"
                                alt="Logo Partner"
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
                            <Link
                                :href="
                                    route('admin.store.show', {
                                        store: store.id,
                                    })
                                "
                                class="hover:underline"
                            >
                                {{ store.name }}
                            </Link>
                        </td>
                        <td>
                            {{ store.email ?? "-" }}
                        </td>
                        <td>
                            {{ store.phone ?? "-" }}
                        </td>
                        <td class="!whitespace-normal">
                            <p class="line-clamp-2">
                                {{ store.address ?? "-" }}
                            </p>
                        </td>
                        <td>
                            <AdminItemAction
                                v-if="canEdit(store)"
                                @edit="
                                    $inertia.visit(
                                        route('admin.store.edit', {
                                            store: store,
                                        })
                                    )
                                "
                                @delete="openDeletePartnerDialog(store)"
                            />
                            <InfoTooltip
                                v-if="!canEdit(store)"
                                :id="`table-tooltip-hint-${store.id}`"
                                text="Partner bawaan sistem"
                            />
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <div
                    v-if="stores.data.length > 0"
                    class="grid grid-cols-1 gap-3"
                >
                    <StoreCard
                        v-for="(store, index) in stores.data"
                        :key="store.id"
                        :store="store"
                        @edit="
                            $inertia.visit(
                                route('admin.store.edit', {
                                    store: store,
                                })
                            )
                        "
                        @delete="openDeletePartnerDialog(store)"
                    />
                </div>
                <div v-else class="flex items-center justify-center py-10">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="stores.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ stores.from }} - {{ stores.to }} dari
                    {{ stores.total }} item
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="stores.links"
                    @change="
                        (page) => {
                            filters.page = page;
                            getPartners();
                        }
                    "
                />
            </div>
        </DefaultCard>

        <DeleteConfirmationDialog
            :show="showDeletePartnerDialog"
            :title="`Hapus Mitra <b>${selectedPartner?.name}</b>?`"
            @close="closeDeletePartnerDialog()"
            @delete="deletePartner()"
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
    </AdminLayout>
</template>

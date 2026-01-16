<script setup lang="ts">
import { ref, onMounted, nextTick, computed } from "vue";
import { usePage, useForm, Link } from "@inertiajs/vue3";
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
import InfoTooltip from "@/Components/InfoTooltip.vue";
import { getImageUrl } from "@/plugins/helpers.js";
import CustomPageProps from "@/types/model/CustomPageProps";
import MyStorePartnerCard from "./MyStorePartnerCard.vue";
import { scrollToTop } from "@/plugins/helpers";
import SearchInput from "@/Components/SearchInput.vue";

const screenSize = useScreenSize();

const props = defineProps({
    partners: {
        type: Object as () => PaginationModel<PartnerEntity>,
        default: null,
    },
});

const partners = ref<PaginationModel<PartnerEntity>>({
    ...props.partners,
    data: props.partners.data.map((partner) => ({
        ...partner,
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
    router.get(route("my-store.partner.index"), queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            partners.value = {
                ...props.partners,
                data: props.partners.data.map((partner) => ({
                    ...partner,
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

const openDeletePartnerDialog = (partner) => {
    console.log("openDeletePartnerDialog", partner);
    if (partner) {
        selectedPartner.value = partner;
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
            route("my-store.partner.destroy", {
                partner: selectedPartner.value,
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

function canEdit(partner) {
    return (
        page.props.auth.is_admin ||
        page.props.auth.user.stores.some(
            (store) => store.id === partner.store_id
        )
    );
}

function setSearchFocus() {
    nextTick(() => {
        const input = document.getElementById(
            "search-partner"
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
    <MyStoreLayout title="Mitra" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="$inertia.visit(route('my-store.partner.create'))"
                >
                    Tambah
                </PrimaryButton>
                <SearchInput
                    id="search-partner"
                    v-model="filters.search"
                    placeholder="Cari mitra..."
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
                :isEmpty="partners.data.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th class="w-24">Logo</th>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Alamat</th>
                        <th>Nama Kontak</th>
                        <th>Email Kontak</th>
                        <th>No. HP Kontak</th>
                        <th class="w-24">Aksi</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr
                        v-for="(partner, index) in partners.data"
                        :key="partner.id"
                    >
                        <td>
                            {{
                                index +
                                1 +
                                (props.partners.current_page - 1) *
                                    props.partners.per_page
                            }}
                        </td>
                        <td>
                            <img
                                v-if="partner.logo"
                                :src="getImageUrl(partner.logo)"
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
                                    route('my-store.partner.show', {
                                        partner: partner.id,
                                    })
                                "
                                class="hover:underline"
                            >
                                {{ partner.name }}
                            </Link>
                        </td>
                        <td class="!whitespace-normal">
                            <p class="line-clamp-2">
                                {{ partner.description ?? "-" }}
                            </p>
                        </td>
                        <td class="!whitespace-normal">
                            <p class="line-clamp-2">
                                {{ partner.address ?? "-" }}
                            </p>
                        </td>
                        <td>
                            {{ partner.contact_name ?? "-" }}
                        </td>
                        <td>
                            {{ partner.contact_email ?? "-" }}
                        </td>
                        <td>
                            {{ partner.contact_phone ?? "-" }}
                        </td>
                        <td>
                            <AdminItemAction
                                v-if="canEdit(partner)"
                                @edit="
                                    $inertia.visit(
                                        route('my-store.partner.edit', {
                                            partner: partner,
                                        })
                                    )
                                "
                                @delete="openDeletePartnerDialog(partner)"
                            />
                            <InfoTooltip
                                v-if="!canEdit(partner)"
                                :id="`table-tooltip-hint-${partner.id}`"
                                text="Partner bawaan sistem"
                            />
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <div
                    v-if="partners.data.length > 0"
                    class="grid grid-cols-1 gap-3"
                >
                    <MyStorePartnerCard
                        v-for="(partner, index) in partners.data"
                        :key="partner.id"
                        :partner="partner"
                        @edit="
                            $inertia.visit(
                                route('my-store.partner.edit', {
                                    partner: partner,
                                })
                            )
                        "
                        @delete="openDeletePartnerDialog(partner)"
                    />
                </div>
                <div v-else class="flex items-center justify-center py-10">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="partners.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ partners.from }} - {{ partners.to }} dari
                    {{ partners.total }} item
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="partners.links"
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
    </MyStoreLayout>
</template>

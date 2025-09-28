<script setup lang="ts">
import { ref, onMounted, nextTick, computed } from "vue";
import { usePage, useForm, router } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import DefaultTable from "@/Components/DefaultTable.vue";
import MyStoreCertificateCard from "./Certificate/MyStoreCertificateCard.vue";
import { useScreenSize } from "@/plugins/screen-size";
import { getImageUrl, scrollToTop } from "@/plugins/helpers";
import CustomPageProps from "@/types/model/CustomPageProps";
import SearchInput from "@/Components/SearchInput.vue";

const screenSize = useScreenSize();

const props = defineProps({
    certificates: Object as () => PaginationModel<StoreCertificateEntity>,
});

const certificates = ref<PaginationModel<StoreCertificateEntity>>({
    ...props.certificates,
    data: props.certificates.data.map((certificate) => ({
        ...certificate,
        showDeleteModal: false,
    })),
});

const selectedCertificate = ref(null);
const showDeleteCertificateDialog = ref(false);

const openDeleteCertificateDialog = (certificate) => {
    if (certificate) {
        selectedCertificate.value = certificate;
        showDeleteCertificateDialog.value = true;
    }
};

const closeDeleteCertificateDialog = (result = false) => {
    showDeleteCertificateDialog.value = false;
    if (result) {
        selectedCertificate.value = null;
        openSuccessDialog("Data Berhasil Dihapus");
    }
};

const deleteCertificate = () => {
    if (selectedCertificate.value) {
        const form = useForm({});
        form.delete(
            route("my-store.certificate.destroy", {
                storeCertificate: selectedCertificate.value,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeleteCertificateDialog(true);
                    getCertificates();
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

function getCertificates() {
    router.get(route("my-store.certificate"), queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            certificates.value = {
                ...page.props.certificates,
                data: page.props.certificates.data.map((certificate) => ({
                    ...certificate,
                    showDeleteModal: false,
                })),
            };
            scrollToTop({ id: "main-area" });
            setSearchFocus();
        },
    });
}

const page = usePage<CustomPageProps>();

function setSearchFocus() {
    nextTick(() => {
        const input = document.getElementById(
            "search-certificate"
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
    <MyStoreLayout title="Sertifikat" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="
                        $inertia.visit(route('my-store.certificate.create'))
                    "
                >
                    Tambah
                </PrimaryButton>
                <SearchInput
                    id="search-certificate"
                    v-model="filters.search"
                    placeholder="Cari sertifikat..."
                    class="max-w-48"
                    @search="
                        filters.page = 1;
                        getCertificates();
                    "
                />
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="certificates.data.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th class="w-[150px]">Foto Sertifikat</th>
                        <th>Nama Sertifikat</th>
                        <th>Deskripsi</th>
                        <th class="w-24 !text-center">Aksi</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr
                        v-for="(certificate, index) in certificates.data"
                        :key="certificate.id"
                    >
                        <td>
                            {{
                                index +
                                1 +
                                (props.certificates.current_page - 1) *
                                    props.certificates.per_page
                            }}
                        </td>
                        <td>
                            <img
                                :src="getImageUrl(certificate.image)"
                                alt="Sertifikat"
                                class="object-cover w-[80px] sm:w-[120px] rounded aspect-[3/2] border border-gray-200"
                            />
                        </td>
                        <td>
                            {{ certificate.name }}
                        </td>
                        <td class="!whitespace-normal">
                            <p class="line-clamp-2">
                                {{ certificate.description }}
                            </p>
                        </td>
                        <td>
                            <AdminItemAction
                                @edit="
                                    $inertia.visit(
                                        route('my-store.certificate.edit', {
                                            storeCertificate: certificate,
                                        })
                                    )
                                "
                                @delete="
                                    openDeleteCertificateDialog(certificate)
                                "
                            />
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <template v-if="certificates.data.length > 0">
                    <div
                        v-for="(certificate, index) in certificates.data"
                        :key="certificate.id"
                    >
                        <MyStoreCertificateCard
                            :certificate="certificate"
                            @edit="
                                $inertia.visit(
                                    route('my-store.certificate.edit', {
                                        storeCertificate: certificate,
                                    })
                                )
                            "
                            @delete="openDeleteCertificateDialog(certificate)"
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
            <div v-if="certificates.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ certificates.from }} -
                    {{ certificates.to }} dari {{ certificates.total }} item
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="certificates.links"
                    @change="getCertificates()"
                />
            </div>
        </DefaultCard>

        <DeleteConfirmationDialog
            :show="showDeleteCertificateDialog"
            :title="`Hapus Sertifikat <b>${selectedCertificate?.name}</b>?`"
            @close="closeDeleteCertificateDialog()"
            @delete="deleteCertificate()"
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

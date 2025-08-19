<script setup>
import { ref, onMounted } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
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

const screenSize = useScreenSize();

const props = defineProps({
    certificates: null,
});

const certificates = ref(
    props.certificates.data.map((certificate) => ({
        ...certificate,
        image: certificate.image ? "/storage/" + certificate.image : null,
        showDeleteModal: false,
    }))
);

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
        certificates.value = certificates.value.filter(
            (cert) => cert.id !== selectedCertificate.value.id
        );
    }
};

const deleteCertificate = () => {
    if (selectedCertificate.value) {
        const form = useForm();
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

onMounted(() => {
    if (page.props.flash.success) {
        openSuccessDialog(page.props.flash.success);
    }
});
</script>

<template>
    <MyStoreLayout title="Sertifikat" :showTitle="true">
        <DefaultCard :isMain="true">
            <PrimaryButton
                type="button"
                class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                @click="$inertia.visit(route('my-store.certificate.create'))"
            >
                Tambah
            </PrimaryButton>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="certificates.length === 0"
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
                        v-for="(certificate, index) in certificates"
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
                                :src="certificate.image"
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
            <div
                v-if="!screenSize.is('xl')"
                class="mt-4 min-h-[68vh] flex flex-col gap-3"
                :class="{ 'min-h-auto h-[68vh]': certificates.length == 0 }"
            >
                <template v-if="certificates.length > 0">
                    <div
                        v-for="(certificate, index) in certificates"
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
                <div v-else class="flex items-center justify-center h-[90%]">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="props.certificates.total > 0"
                class="flex flex-col gap-2 mt-4"
            >
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ props.certificates.from }} -
                    {{ props.certificates.to }} dari
                    {{ props.certificates.total }} item
                </p>
                <DefaultPagination :links="props.certificates.links" />
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

<script setup>
import { ref, onMounted } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminPagination from "@/Components/AdminPagination.vue";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import { getImageUrl } from "@/plugins/helpers";

const props = defineProps({
    certificates: null,
});

const certificates = ref(
    props.certificates.data.map((certificate) => ({
        ...certificate,
        showDeleteModal: false,
    })),
);

const showDeleteCertificateDialog = (certificate) => {
    if (certificate) {
        certificate.showDeleteModal = true;
        console.log(`Deleting certificate with ID: ${certificate}`);
    }
};

const closeDeleteCertificateDialog = (certificate, result) => {
    if (certificate) {
        certificate.showDeleteModal = false;
        if (result) {
            openSuccessDialog("Data Berhasil Dihapus");
            certificates.value = certificates.value.filter(
                (cert) => cert.id !== certificate.id,
            );
        }
    }
};

const deleteCertificate = (certificate) => {
    if (certificate) {
        const form = useForm();
        form.delete(
            route("admin.certificate.destroy", {
                storeCertificate: certificate,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeleteCertificateDialog(certificate, true);
                },
            },
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
    <AdminLayout title="Sertifikat" :showTitle="true">
        <template #icon>
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="26"
                height="27"
                viewBox="0 0 26 27"
                class="fill-primary"
            >
                <rect opacity="0.01" y="0.695312" width="26" height="26" />
                <path
                    d="M15.7737 2.8625C16.0793 2.86325 16.3707 2.9931 16.5755 3.21992L21.3851 8.63691C21.5656 8.8355 21.6665 9.09414 21.6673 9.3625V21.8215C21.6587 22.5483 21.3612 23.2423 20.8411 23.7502C20.3211 24.2579 19.6206 24.5381 18.8939 24.5295H7.10675C6.38003 24.5381 5.67952 24.2579 5.15948 23.7502C4.63944 23.2423 4.34196 22.5483 4.33331 21.8215V5.57148C4.34188 4.84456 4.63938 4.15069 5.15948 3.64277C5.67954 3.1349 6.3799 2.85389 7.10675 2.8625H15.7737ZM9.75031 18.0295C9.15205 18.0295 8.66739 18.5143 8.6673 19.1125C8.6673 19.7108 9.152 20.1955 9.75031 20.1955H16.2503C16.8486 20.1955 17.3333 19.7108 17.3333 19.1125C17.3332 18.5143 16.8486 18.0295 16.2503 18.0295H9.75031ZM9.75031 13.6965C9.152 13.6965 8.6673 14.1812 8.6673 14.7795C8.66739 15.3777 9.15205 15.8625 9.75031 15.8625H13.0003C13.5986 15.8625 14.0832 15.3777 14.0833 14.7795C14.0833 14.1812 13.5986 13.6965 13.0003 13.6965H9.75031ZM15.1663 8.4416C15.1286 8.90696 15.4712 9.31674 15.9359 9.3625H19.2181L15.1663 5.02949V8.4416Z"
                />
            </svg>
        </template>

        <div>
            <PrimaryButton
                type="button"
                class="bg-yellow-500 hover:bg-yellow-500/80 active:bg-yellow-500/90 focus:bg-yellow-500 focus:ring-yellow-500 max-sm:text-xs max-sm:px-4 max-sm:py-2"
                @click="$inertia.visit(route('admin.certificate.create'))"
            >
                + Tambah Data</PrimaryButton
            >

            <!-- Table -->
            <div class="mt-4 overflow-x-auto rounded-t-lg sm:mt-6">
                <table class="table-default">
                    <thead>
                        <tr>
                            <th class="w-12">No</th>
                            <th class="min-w-[150px] w-[200px]">
                                Foto Sertifikat
                            </th>
                            <th>Nama Sertifikat</th>
                            <th>Deskripsi</th>
                            <th class="w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
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
                                    :src="getImageUrl(certificate.image)"
                                    :alt="certificate.name"
                                    class="object-cover w-[100px] sm:w-[160px] rounded aspect-[3/2]"
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
                                            route('admin.certificate.edit', {
                                                storeCertificate: certificate,
                                            }),
                                        )
                                    "
                                    @delete="
                                        showDeleteCertificateDialog(certificate)
                                    "
                                />
                                <DeleteConfirmationDialog
                                    :show="certificate.showDeleteModal"
                                    @close="
                                        closeDeleteCertificateDialog(
                                            certificate,
                                        )
                                    "
                                    @delete="deleteCertificate(certificate)"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>

                <SuccessDialog
                    :show="showSuccessDialog"
                    :title="successMessage"
                    @close="showSuccessDialog = false"
                />

                <ErrorDialog
                    :show="showErrorDialog"
                    @close="showErrorDialog = false"
                >
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
            </div>

            <!-- Pagination -->
            <AdminPagination :links="props.certificates.links" />
        </div>
    </AdminLayout>
</template>

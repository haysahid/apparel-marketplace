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
                (cert) => cert.id !== certificate.id
            );
        }
    }
};

const deleteCertificate = (certificate) => {
    if (certificate) {
        const form = useForm();
        form.delete(
            route("my-store.certificate.destroy", {
                storeCertificate: certificate,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeleteCertificateDialog(certificate, true);
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
        <DefaultCard>
            <PrimaryButton
                type="button"
                class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                @click="$inertia.visit(route('my-store.certificate.create'))"
            >
                Tambah
            </PrimaryButton>

            <!-- Table -->
            <div
                class="hidden mt-4 overflow-x-auto border border-gray-100 rounded-md sm:mt-6 md:block min-h-[60vh]"
            >
                <table class="table-default">
                    <thead>
                        <tr>
                            <th class="w-12">No</th>
                            <th class="w-[150px]">Foto Sertifikat</th>
                            <th>Nama Sertifikat</th>
                            <th>Deskripsi</th>
                            <th class="w-24 !text-center">Aksi</th>
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
                                        showDeleteCertificateDialog(certificate)
                                    "
                                />
                                <DeleteConfirmationDialog
                                    :show="certificate.showDeleteModal"
                                    @close="
                                        closeDeleteCertificateDialog(
                                            certificate
                                        )
                                    "
                                    @delete="deleteCertificate(certificate)"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="mt-4 md:hidden min-h-[60vh] flex flex-col gap-2">
                <div
                    v-for="(certificate, index) in certificates"
                    :key="certificate.id"
                    class="flex items-center justify-between gap-4 p-4 border border-gray-100 rounded-md"
                >
                    <img
                        :src="certificate.image"
                        alt="Sertifikat"
                        class="object-cover w-[80px] rounded aspect-[3/2] border border-gray-200"
                    />
                    <div class="flex flex-col w-full gap-1">
                        <p class="text-sm font-medium text-gray-900">
                            {{ certificate.name }}
                        </p>
                        <p
                            class="text-xs text-gray-500 line-clamp-2 overflow-ellipsis"
                        >
                            {{ certificate.description }}
                        </p>
                    </div>

                    <AdminItemAction
                        @edit="
                            $inertia.visit(
                                route('my-store.certificate.edit', {
                                    storeCertificate: certificate,
                                })
                            )
                        "
                        @delete="showDeleteCertificateDialog(certificate)"
                    />

                    <DeleteConfirmationDialog
                        :show="certificate.showDeleteModal"
                        @close="closeDeleteCertificateDialog(certificate)"
                        @delete="deleteCertificate(certificate)"
                    />
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

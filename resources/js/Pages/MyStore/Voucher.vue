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
import MyStoreVoucherCard from "./Voucher/MyStoreVoucherCard.vue";
import { useScreenSize } from "@/plugins/screen-size";

const screenSize = useScreenSize();

const props = defineProps({
    vouchers: null,
});

const vouchers = ref(
    props.vouchers.data.map((voucher) => ({
        ...voucher,
        image: voucher.image ? "/storage/" + voucher.image : null,
        showDeleteModal: false,
    }))
);

const selectedVoucher = ref(null);
const showDeleteVoucherDialog = ref(false);

const openDeleteVoucherDialog = (voucher) => {
    if (voucher) {
        selectedVoucher.value = voucher;
        showDeleteVoucherDialog.value = true;
    }
};

const closeDeleteVoucherDialog = (result = false) => {
    showDeleteVoucherDialog.value = false;
    if (result) {
        openSuccessDialog("Data Berhasil Dihapus");
        vouchers.value = vouchers.value.filter(
            (cert) => cert.id !== selectedVoucher.value.id
        );
        selectedVoucher.value = null;
    }
};

const deleteVoucher = () => {
    if (selectedVoucher.value) {
        const form = useForm();
        form.delete(
            route("my-store.voucher.destroy", {
                voucher: selectedVoucher.value,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeleteVoucherDialog(true);
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
    <MyStoreLayout title="Voucher" :showTitle="true">
        <DefaultCard :isMain="true">
            <PrimaryButton
                type="button"
                class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                @click="$inertia.visit(route('my-store.voucher.create'))"
            >
                Tambah
            </PrimaryButton>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="vouchers.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Nama Voucher</th>
                        <th class="w-[150px]">Jumlah Diskon</th>
                        <th>Limit</th>
                        <th>Tgl. Mulai</th>
                        <th>Tgl. Berakhir</th>
                        <th class="w-24 !text-center">Aksi</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr v-for="(voucher, index) in vouchers" :key="voucher.id">
                        <td>
                            {{
                                index +
                                1 +
                                (props.vouchers.current_page - 1) *
                                    props.vouchers.per_page
                            }}
                        </td>
                        <td>
                            {{ voucher.name }}
                        </td>
                        <td>
                            {{ voucher.amount }}
                            {{ voucher.type === "percentage" ? "%" : "Rp" }}
                        </td>
                        <td>
                            {{ voucher.usage_limit }}
                        </td>
                        <td>
                            {{
                                $formatDate(voucher.start_date, {
                                    dateStyle: "medium",
                                })
                            }}
                        </td>
                        <td>
                            {{
                                $formatDate(voucher.end_date, {
                                    dateStyle: "medium",
                                })
                            }}
                        </td>
                        <td>
                            <AdminItemAction
                                @edit="
                                    $inertia.visit(
                                        route('my-store.voucher.edit', {
                                            voucher: voucher,
                                        })
                                    )
                                "
                                @delete="openDeleteVoucherDialog(voucher)"
                            />
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div
                v-if="!screenSize.is('xl')"
                class="mt-4 min-h-[68vh] flex flex-col gap-3"
                :class="{ 'min-h-auto h-[68vh]': vouchers.length == 0 }"
            >
                <template v-if="vouchers.length > 0">
                    <div v-for="(voucher, index) in vouchers" :key="voucher.id">
                        <MyStoreVoucherCard
                            :voucher="voucher"
                            @edit="
                                $inertia.visit(
                                    route('my-store.voucher.edit', {
                                        voucher: voucher,
                                    })
                                )
                            "
                            @delete="openDeleteVoucherDialog(voucher)"
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
                v-if="props.vouchers.total > 0"
                class="flex flex-col gap-2 mt-6"
            >
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ props.vouchers.from }} -
                    {{ props.vouchers.to }} dari {{ props.vouchers.total }} item
                </p>
                <DefaultPagination :links="props.vouchers.links" />
            </div>
        </DefaultCard>

        <DeleteConfirmationDialog
            :show="showDeleteVoucherDialog"
            :title="`Hapus Voucher <b>${selectedVoucher?.name}</b>?`"
            @close="closeDeleteVoucherDialog()"
            @delete="deleteVoucher()"
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

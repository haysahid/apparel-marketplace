<script setup lang="ts">
import { ref, onMounted, nextTick } from "vue";
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
import { useScreenSize } from "@/plugins/screen-size";
import AdminItemCard from "@/Components/AdminItemCard.vue";
import CustomPageProps from "@/types/model/CustomPageProps";
import TextInput from "@/Components/TextInput.vue";

const screenSize = useScreenSize();

const props = defineProps({
    vouchers: {
        type: Object as () => PaginationModel<VoucherEntity>,
        required: true,
    },
});

const vouchers = ref(
    props.vouchers.data.map((voucher) => ({
        ...voucher,
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
        const form = useForm({});
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

const filters = useForm({
    search: null,
});

const getQueryParams = () => {
    filters.search = route().params.search;
};

getQueryParams();

function getVouchers() {
    let queryParams = {
        search: undefined,
    };

    if (filters.search) queryParams.search = filters.search;

    router.get(route("my-store.voucher"), queryParams, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            vouchers.value = props.vouchers.data.map((voucher) => ({
                ...voucher,
                showDeleteModal: false,
            }));
        },
    });
}

const page = usePage<CustomPageProps>();

onMounted(() => {
    if (page.props.flash.success) {
        openSuccessDialog(page.props.flash.success);
    }

    nextTick(() => {
        const input = document.getElementById(
            "search-voucher"
        ) as HTMLInputElement;
        input?.focus();
    });
});
</script>

<template>
    <MyStoreLayout title="Voucher" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-2">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="$inertia.visit(route('my-store.voucher.create'))"
                >
                    Tambah
                </PrimaryButton>
                <TextInput
                    id="search-voucher"
                    v-model="filters.search"
                    placeholder="Cari voucher..."
                    class="max-w-48"
                    @keyup.enter="getVouchers()"
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
                :isEmpty="vouchers.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Nama Voucher</th>
                        <th>Kode Voucher</th>
                        <th class="w-[150px]">Jumlah Diskon</th>
                        <th>Limit</th>
                        <th>Tgl. Mulai</th>
                        <th>Tgl. Berakhir</th>
                        <th class="w-24">Aksi</th>
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
                            {{ voucher.code }}
                        </td>
                        <td>
                            {{
                                voucher.type === "percentage"
                                    ? `${voucher.amount}%`
                                    : $formatCurrency(voucher.amount)
                            }}
                        </td>
                        <td>
                            {{ voucher.usage_limit ?? "-" }}
                        </td>
                        <td>
                            {{
                                $formatDate(voucher.redeem_start_date, {
                                    dateStyle: "medium",
                                }) ?? "-"
                            }}
                        </td>
                        <td>
                            {{
                                $formatDate(voucher.redeem_end_date, {
                                    dateStyle: "medium",
                                }) ?? "-"
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
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <div
                    v-if="vouchers.length > 0"
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                >
                    <AdminItemCard
                        v-for="(voucher, index) in vouchers"
                        :key="voucher.id"
                        :name="voucher.name"
                        :description="`${voucher.code} - ${
                            voucher.type === 'percentage'
                                ? voucher.amount + '%'
                                : $formatCurrency(voucher.amount)
                        }`"
                        :showImage="false"
                        @edit="
                            $inertia.visit(
                                route('my-store.voucher.edit', {
                                    voucher: voucher,
                                })
                            )
                        "
                        @delete="openDeleteVoucherDialog(voucher)"
                    >
                        <template #leading>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="26"
                                height="26"
                                viewBox="0 0 26 26"
                                class="fill-gray-500 size-8 shrink-0"
                            >
                                <path
                                    d="M4.33317 4.33325C3.75853 4.33325 3.20743 4.56153 2.80111 4.96785C2.39478 5.37418 2.1665 5.92528 2.1665 6.49992V10.8333C2.74114 10.8333 3.29224 11.0615 3.69857 11.4679C4.1049 11.8742 4.33317 12.4253 4.33317 12.9999C4.33317 13.5746 4.1049 14.1257 3.69857 14.532C3.29224 14.9383 2.74114 15.1666 2.1665 15.1666V19.4999C2.1665 20.0746 2.39478 20.6257 2.80111 21.032C3.20743 21.4383 3.75853 21.6666 4.33317 21.6666H21.6665C22.2411 21.6666 22.7922 21.4383 23.1986 21.032C23.6049 20.6257 23.8332 20.0746 23.8332 19.4999V15.1666C23.2585 15.1666 22.7074 14.9383 22.3011 14.532C21.8948 14.1257 21.6665 13.5746 21.6665 12.9999C21.6665 12.4253 21.8948 11.8742 22.3011 11.4679C22.7074 11.0615 23.2585 10.8333 23.8332 10.8333V6.49992C23.8332 5.92528 23.6049 5.37418 23.1986 4.96785C22.7922 4.56153 22.2411 4.33325 21.6665 4.33325H4.33317ZM16.7915 7.58325L18.4165 9.20825L9.20817 18.4166L7.58317 16.7916L16.7915 7.58325ZM9.544 7.62659C10.6057 7.62659 11.4615 8.48242 11.4615 9.54409C11.4615 10.0526 11.2595 10.5404 10.8999 10.9C10.5403 11.2596 10.0526 11.4616 9.544 11.4616C8.48234 11.4616 7.6265 10.6058 7.6265 9.54409C7.6265 9.03553 7.82853 8.54781 8.18813 8.18821C8.54773 7.82861 9.03545 7.62659 9.544 7.62659ZM16.4557 14.5383C17.5173 14.5383 18.3732 15.3941 18.3732 16.4558C18.3732 16.9643 18.1711 17.452 17.8115 17.8116C17.4519 18.1712 16.9642 18.3733 16.4557 18.3733C15.394 18.3733 14.5382 17.5174 14.5382 16.4558C14.5382 15.9472 14.7402 15.4595 15.0998 15.0999C15.4594 14.7403 15.9471 14.5383 16.4557 14.5383Z"
                                />
                            </svg>
                        </template>
                    </AdminItemCard>
                </div>
                <div v-else class="flex items-center justify-center h-[40vh]">
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

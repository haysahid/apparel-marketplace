<script setup lang="ts">
import { ref, onMounted, computed, nextTick } from "vue";
import { useForm, router, Link, usePage } from "@inertiajs/vue3";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import TextInput from "@/Components/TextInput.vue";
import StatusChip from "@/Components/StatusChip.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import DefaultTable from "@/Components/DefaultTable.vue";
import { useScreenSize } from "@/plugins/screen-size";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import DialogModal from "@/Components/DialogModal.vue";
import MyPaymentCard from "./Payment/MyPaymentCard.vue";
import CustomPageProps from "@/types/model/CustomPageProps";
import SearchInput from "@/Components/SearchInput.vue";
import { scrollToTop } from "@/plugins/helpers";

const screenSize = useScreenSize();

const props = defineProps({
    payments: {
        type: Object as () => PaginationModel<PaymentEntity>,
        required: true,
    },
    transactionTypes: {
        type: Array as () => TransactionTypeEntity[],
        required: true,
    },
});

const page = usePage<CustomPageProps>();

const payments = ref<PaginationModel<PaymentEntity>>({
    ...props.payments,
    data: props.payments.data.map((payment) => ({
        ...payment,
        showDeleteModal: false,
    })),
});

const closeDeleteTransactionDialog = (transaction, result) => {
    if (transaction) {
        transaction.showDeleteModal = false;
        if (result) {
            openSuccessDialog("Data Berhasil Dihapus");
        }
    }
};

const deleteTransaction = (transaction) => {
    if (transaction) {
        const form = useForm({});
        form.delete(
            route("my-store.transaction.destroy", {
                transaction: transaction,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeleteTransactionDialog(transaction, true);
                    getPayments();
                },
            }
        );
    }
};

const showSuccessDialog = ref(false);
const successMessage = ref("Data Berhasil Dihapus");

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

// Filters
const transactionTypes = (page.props.transactionTypes ||
    []) as TransactionTypeEntity[];
const transactionTypeSearch = ref("");

const filteredTransactionTypes = computed(() => {
    return transactionTypes.filter((type) =>
        type.name
            .toLowerCase()
            .includes(transactionTypeSearch.value?.toLowerCase() || "")
    );
});

const filters = useForm({
    page: null,
    search: null,
    transaction_type_id: null,
    transaction_type: null,
});

const getQueryParams = () => {
    filters.page = parseInt(route().params.page) || null;
    filters.search = route().params.search || null;
    filters.transaction_type_id = parseInt(route().params.type_id) || null;
    filters.transaction_type =
        props.transactionTypes.find(
            (type) => type.id === filters.transaction_type_id
        ) || null;
};
getQueryParams();

const queryParams = computed(() => {
    return {
        page: filters.page || undefined,
        search: filters.search || undefined,
        type_id: filters.transaction_type_id || undefined,
    };
});

function getPayments() {
    router.get(route("my-store.payment"), queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            payments.value = {
                ...page.props.payments,
                data: page.props.payments.data.map((payment) => ({
                    ...payment,
                    showDeleteModal: false,
                })),
            };
            scrollToTop({ id: "main-area" });
            setSearchFocus();
        },
    });
}

function setSearchFocus() {
    nextTick(() => {
        const input = document.getElementById(
            "search-payment"
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

const showPaymentStatusOptionDialog = ref(false);
</script>

<template>
    <MyStoreLayout title="Pembayaran" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-end gap-4">
                <div class="flex items-center gap-2">
                    <DropdownSearchInput
                        id="transaction_type_id"
                        :modelValue="
                            filters.transaction_type_id
                                ? {
                                      label: filters.transaction_type?.name,
                                      value: filters.transaction_type_id,
                                  }
                                : null
                        "
                        :options="
                            filteredTransactionTypes.map((type) => ({
                                label: type.name,
                                value: type.id,
                            }))
                        "
                        placeholder="Pilih Jenis Transaksi"
                        class="max-w-48"
                        :error="filters.errors.transaction_type_id"
                        @update:modelValue="
                            (option) => {
                                filters.transaction_type_id = option?.value;
                                filters.transaction_type = option
                                    ? filteredTransactionTypes.find(
                                          (type) => type.id === option.value
                                      )
                                    : null;
                                filters.page = 1;
                                getPayments();
                            }
                        "
                        @search="transactionTypeSearch = $event"
                        @clear="
                            filters.transaction_type_id = null;
                            filters.transaction_type = null;
                            transactionTypeSearch = '';
                            filters.page = 1;
                            getPayments();
                        "
                    />
                    <SearchInput
                        id="search-payment"
                        v-model="filters.search"
                        placeholder="Cari pembayaran..."
                        class="max-w-48"
                        @search="
                            filters.page = 1;
                            getPayments();
                        "
                    />
                </div>
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="payments.data.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Tanggal</th>
                        <th>Kode Transaksi</th>
                        <th>Oleh</th>
                        <th>Metode</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Diterima</th>
                        <!-- <th class="w-24">Aksi</th> -->
                    </tr>
                </template>
                <template #tbody>
                    <tr
                        v-for="(payment, index) in payments.data"
                        :key="payment.id"
                    >
                        <td>
                            {{
                                index +
                                1 +
                                (props.payments.current_page - 1) *
                                    props.payments.per_page
                            }}
                        </td>
                        <td>
                            {{ $formatDate(payment.created_at) }}
                        </td>
                        <td>
                            <Link
                                :href="
                                    route('my-store.transaction.edit', {
                                        transaction: payment.transaction,
                                    })
                                "
                                class="hover:underline"
                            >
                                {{ payment.transaction.code }}
                            </Link>
                        </td>
                        <td class="!whitespace-normal">
                            {{ payment.transaction.user.name }}
                        </td>
                        <td>
                            {{ payment.payment_method.name }}
                        </td>
                        <td>
                            {{
                                $formatCurrency(
                                    payment.midtrans_response?.gross_amount
                                )
                            }}
                        </td>
                        <td>
                            <StatusChip
                                :status="payment.status"
                                :label="payment.status.toLocaleUpperCase()"
                                class="w-fit"
                            />
                        </td>
                        <td>
                            {{
                                payment.status === "completed" &&
                                payment.transaction.paid_at &&
                                payment.midtrans_response?.va_numbers[0]?.bank
                                    ? `
                                    ${
                                        payment.midtrans_response?.va_numbers[0]?.bank.toUpperCase() ??
                                        ""
                                    } - 
                                 `
                                    : ""
                            }}
                            {{
                                $formatDate(payment.transaction.paid_at) ?? "-"
                            }}
                        </td>
                        <!-- <td>
                            <AdminItemAction
                                @edit="showPaymentStatusOptionDialog = true"
                            />
                        </td> -->
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <template v-if="payments.data.length > 0">
                    <div
                        v-for="(payment, index) in payments.data"
                        :key="payment.id"
                    >
                        <MyPaymentCard :payment="payment" />
                    </div>
                </template>
                <div v-else class="flex items-center justify-center py-10">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="payments.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ payments.from }} - {{ payments.to }} dari
                    {{ payments.total }} item
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="payments.links"
                    @change="
                        (page) => {
                            filters.page = page;
                            getPayments();
                        }
                    "
                />
            </div>

            <DialogModal
                :show="showPaymentStatusOptionDialog"
                @close="showPaymentStatusOptionDialog = false"
            >
                <template #title> Pilih Jenis Transaksi </template>
                <template #content>
                    <div
                        class="grid w-full grid-cols-2 gap-2 mt-2 sm:grid-cols-4"
                    >
                        <template
                            v-for="(status, index) in [
                                'pending',
                                'completed',
                                'failed',
                            ]"
                            :key="index"
                        >
                            <button
                                type="button"
                                class="w-full px-4 py-2 text-center transition bg-white border rounded-lg shadow-sm hover:bg-gray-50 min-h-16"
                                @click="showPaymentStatusOptionDialog = false"
                            >
                                {{ status.toLocaleUpperCase() }}
                            </button>
                        </template>
                    </div>
                </template>
            </DialogModal>

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
        </DefaultCard>
    </MyStoreLayout>
</template>

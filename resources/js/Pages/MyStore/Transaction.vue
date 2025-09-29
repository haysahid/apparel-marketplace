<script setup lang="ts">
import { ref, onMounted, computed, nextTick } from "vue";
import { useForm, router, Link, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import TextInput from "@/Components/TextInput.vue";
import StatusChip from "@/Components/StatusChip.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import DefaultTable from "@/Components/DefaultTable.vue";
import { useScreenSize } from "@/plugins/screen-size";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import MyTransactionCard from "./Transaction/MyTransactionCard.vue";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import DialogModal from "@/Components/DialogModal.vue";
import CustomPageProps from "@/types/model/CustomPageProps";
import { scrollToTop } from "@/plugins/helpers";
import SearchInput from "@/Components/SearchInput.vue";

const screenSize = useScreenSize();

const props = defineProps({
    transactions: {
        type: Object as () => PaginationModel<TransactionEntity>,
        required: true,
    },
    transactionTypes: {
        type: Array as () => TransactionTypeEntity[],
        required: true,
    },
    brands: {
        type: Array as () => BrandEntity[],
        required: true,
    },
});

const page = usePage<CustomPageProps>();

const transactions = ref<PaginationModel<TransactionEntity>>({
    ...props.transactions,
    data: props.transactions.data.map((transaction) => ({
        ...transaction,
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

const brands = (page.props.brands || []) as BrandEntity[];
const brandSearch = ref("");

const filteredBrands = computed(() => {
    return brands.filter((brand) =>
        brand.name
            .toLowerCase()
            .includes(brandSearch.value?.toLowerCase() || "")
    );
});

const filters = useForm({
    page: null,
    search: null,
    transaction_type_id: null,
    transaction_type: null,
    brand_id: null,
    brand: null,
});

const getQueryParams = () => {
    filters.page = parseInt(route().params.page) || null;
    filters.search = route().params.search || null;
    filters.transaction_type_id = parseInt(route().params.type_id) || null;
    filters.transaction_type =
        props.transactionTypes.find(
            (type) => type.id === filters.transaction_type_id
        ) || null;
    filters.brand_id = parseInt(route().params.brand_id) || null;
    filters.brand =
        props.brands.find((brand) => brand.id === filters.brand_id) || null;
};
getQueryParams();

const queryParams = computed(() => {
    return {
        page: filters.page || undefined,
        search: filters.search || undefined,
        type_id: filters.transaction_type_id || undefined,
        brand_id: filters.brand_id || undefined,
    };
});

function getTransactions() {
    router.get(route("my-store.transaction"), queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            transactions.value = {
                ...page.props.transactions,
                data: page.props.transactions.data.map((transaction) => ({
                    ...transaction,
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
            "search-transaction"
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

const showTransactionTypeOptionDialog = ref(false);
</script>

<template>
    <MyStoreLayout title="Transaksi" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="showTransactionTypeOptionDialog = true"
                >
                    Tambah
                </PrimaryButton>

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
                        placeholder="Pilih Jenis"
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
                                getTransactions();
                            }
                        "
                        @search="transactionTypeSearch = $event"
                        @clear="
                            filters.transaction_type_id = null;
                            filters.transaction_type = null;
                            transactionTypeSearch = '';
                            filters.page = 1;
                            getTransactions();
                        "
                    />
                    <DropdownSearchInput
                        id="brand_id"
                        :modelValue="
                            filters.brand_id
                                ? {
                                      label: filters.brand?.name,
                                      value: filters.brand_id,
                                  }
                                : null
                        "
                        :options="
                            filteredBrands.map((brand) => ({
                                label: brand.name,
                                value: brand.id,
                            }))
                        "
                        placeholder="Pilih Brand"
                        class="max-w-48"
                        :error="filters.errors.brand_id"
                        @update:modelValue="
                            (option) => {
                                filters.brand_id = option?.value;
                                filters.brand = option
                                    ? filteredBrands.find(
                                          (brand) => brand.id === option.value
                                      )
                                    : null;
                                filters.page = 1;
                                getTransactions();
                            }
                        "
                        @search="brandSearch = $event"
                        @clear="
                            filters.brand_id = null;
                            filters.brand = null;
                            brandSearch = '';
                            filters.page = 1;
                            getTransactions();
                        "
                    />
                    <SearchInput
                        id="search-transaction"
                        v-model="filters.search"
                        placeholder="Cari transaksi..."
                        class="max-w-48"
                        @search="
                            filters.page = 1;
                            getTransactions();
                        "
                    />
                </div>
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="transactions.data.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Tanggal</th>
                        <th>Kode</th>
                        <th>Jenis</th>
                        <th>Oleh</th>
                        <th>Item</th>
                        <th>Pembayaran</th>
                        <th>Pengiriman</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="w-24">Aksi</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr
                        v-for="(transaction, index) in transactions.data"
                        :key="transaction.id"
                    >
                        <td>
                            {{
                                index +
                                1 +
                                (props.transactions.current_page - 1) *
                                    props.transactions.per_page
                            }}
                        </td>
                        <td>
                            {{ $formatDate(transaction.created_at) }}
                        </td>
                        <td>
                            <Link
                                :href="
                                    route('my-store.transaction.edit', {
                                        transaction: transaction,
                                    })
                                "
                                class="hover:underline"
                            >
                                {{ transaction.code }}
                            </Link>
                        </td>
                        <td>
                            {{ transaction.type?.name }}
                        </td>
                        <td class="!whitespace-normal">
                            {{ transaction.user.name }}
                        </td>
                        <td>
                            {{ transaction.items.length }}
                        </td>
                        <td>
                            {{ transaction.payment_method.name }}
                        </td>
                        <td>
                            {{ transaction.shipping_method.name }}
                        </td>
                        <td>
                            {{
                                $formatCurrency(
                                    transaction.items.reduce(
                                        (total, item) => total + item.subtotal,
                                        0
                                    )
                                )
                            }}
                        </td>
                        <td>
                            <StatusChip
                                :status="transaction.status"
                                :label="transaction.status.toLocaleUpperCase()"
                                class="w-fit"
                            />
                        </td>
                        <td>
                            <AdminItemAction
                                @edit="
                                    $inertia.visit(
                                        route('my-store.transaction.edit', {
                                            transaction: transaction,
                                        })
                                    )
                                "
                            />
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <template v-if="transactions.data.length > 0">
                    <div
                        v-for="(transaction, index) in transactions.data"
                        :key="transaction.id"
                    >
                        <MyTransactionCard
                            :transaction="transaction"
                            @edit="
                                $inertia.visit(
                                    route('my-store.transaction.edit', {
                                        transaction: transaction,
                                    })
                                )
                            "
                        />
                    </div>
                </template>
                <div v-else class="flex items-center justify-center py-10">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="transactions.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ transactions.from }} -
                    {{ transactions.to }} dari {{ transactions.total }} item
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="transactions.links"
                    @change="
                        (page) => {
                            filters.page = page;
                            getTransactions();
                        }
                    "
                />
            </div>

            <DialogModal
                :show="showTransactionTypeOptionDialog"
                @close="showTransactionTypeOptionDialog = false"
            >
                <template #title> Pilih Jenis Transaksi </template>
                <template #content>
                    <div
                        class="grid w-full grid-cols-2 gap-2 mt-2 sm:grid-cols-4"
                    >
                        <template
                            v-for="type in [
                                {
                                    name: 'Pembelian',
                                    slug: 'purchase',
                                    effect_on_stock: 'inbound',
                                },
                                {
                                    name: 'Penjualan',
                                    slug: 'sale',
                                    effect_on_stock: 'outbound',
                                },
                                {
                                    name: 'Barang Hilang',
                                    slug: 'damaged-out',
                                    effect_on_stock: 'outbound',
                                },
                                {
                                    name: 'Penggunaan Internal',
                                    slug: 'internal-use',
                                    effect_on_stock: 'outbound',
                                },
                            ]"
                            :key="type.id"
                        >
                            <button
                                type="button"
                                class="w-full px-4 py-2 text-center transition bg-white border rounded-lg shadow-sm hover:bg-gray-50 min-h-16"
                                @click="
                                    showTransactionTypeOptionDialog = false;
                                    $inertia.visit(
                                        route('my-store.order.create', {
                                            type: type.slug,
                                        })
                                    );
                                "
                            >
                                {{ type.name }}
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

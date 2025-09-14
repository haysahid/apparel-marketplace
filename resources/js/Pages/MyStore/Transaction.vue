<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { usePage, useForm, router, Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminPagination from "@/Components/AdminPagination.vue";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import TextInput from "@/Components/TextInput.vue";
import Dropdown from "@/Components/Dropdown.vue";
import OrderStatusChip from "../Order/OrderStatusChip.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import DefaultTable from "@/Components/DefaultTable.vue";
import { useScreenSize } from "@/plugins/screen-size";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import MyTransactionCard from "./Transaction/MyTransactionCard.vue";
import DefaultPagination from "@/Components/DefaultPagination.vue";

const screenSize = useScreenSize();

const props = defineProps({
    transactions: {
        type: Object as () => {
            data: TransactionEntity[];
            current_page: number;
            per_page: number;
            total: number;
            links: Array<{ url: string; label: string; active: boolean }>;
        },
        required: true,
    },
    brands: {
        type: Array as () => BrandEntity[],
        required: true,
    },
});

const page = usePage();

const transactions = ref(
    props.transactions.data.map((transaction) => ({
        ...transaction,
        showDeleteModal: false,
    }))
);

const showDeleteTransactionDialog = (id) => {
    const transaction = transactions.value.find((item) => item.id === id);
    if (transaction) {
        transaction.showDeleteModal = true;
        console.log(`Deleting transaction with ID: ${transaction.id}`);
    }
};

const closeDeleteTransactionDialog = (transaction, result) => {
    if (transaction) {
        transaction.showDeleteModal = false;
        if (result) {
            openSuccessDialog("Data Berhasil Dihapus");
            transactions.value = transactions.value.filter(
                (item) => item.id !== transaction.id
            );
        }
    }
};

const deleteTransaction = (transaction) => {
    if (transaction) {
        const form = useForm();
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
const brands = page.props.brands || [];
const brandSearch = ref("");
const isBrandDropdownOpen = ref(false);

const filteredBrands = computed(() => {
    return brands.filter((brand) =>
        brand.name
            .toLowerCase()
            .includes(brandSearch.value?.toLowerCase() || "")
    );
});

const filters = useForm({
    search: null,
    brand_id: null,
    brand: null,
});

const getQueryParams = () => {
    filters.search = route().params.search;
    filters.brand_id = parseInt(route().params.brand_id) || null;
    filters.brand =
        props.brands.find((brand) => brand.id === filters.brand_id) || null;
};
getQueryParams();

function getTransactions() {
    let queryParams = {};

    if (filters.search) queryParams.search = filters.search;
    if (filters.brand_id) queryParams.brand_id = filters.brand_id;

    router.get(route("my-store.transaction"), queryParams, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            transactions.value = props.transactions.data.map((transaction) => ({
                ...transaction,
                showDeleteModal: false,
            }));
        },
    });
}

onMounted(() => {
    if (page.props.flash.success) {
        openSuccessDialog(page.props.flash.success);
    }
});
</script>

<template>
    <MyStoreLayout title="Pesanan" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="$inertia.visit(route('my-store.product.create'))"
                >
                    Tambah
                </PrimaryButton>

                <div class="flex items-center gap-2">
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
                                          (brand) => brand.id === brand.value
                                      )
                                    : null;
                                getTransactions();
                            }
                        "
                        @search="brandSearch = $event"
                        @clear="
                            filters.brand_id = null;
                            filters.brand = null;
                            brandSearch = '';
                            getTransactions();
                        "
                    />
                    <TextInput
                        v-model="filters.search"
                        placeholder="Cari transaksi..."
                        class="max-w-48"
                        @keyup.enter="getTransactions()"
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
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="transactions.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Tanggal</th>
                        <th>Kode</th>
                        <th>Pemesan</th>
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
                        v-for="(transaction, index) in transactions"
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
                        <td class="text-center">
                            {{ transaction.user.name }}
                        </td>
                        <td class="text-center">
                            {{ transaction.items.length }}
                        </td>
                        <td class="text-center">
                            {{ transaction.payment_method.name }}
                        </td>
                        <td class="text-center">
                            {{ transaction.shipping_method.name }}
                        </td>
                        <td class="text-center">
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
                            <div class="flex items-center justify-center">
                                <OrderStatusChip
                                    :status="transaction.status"
                                    :label="
                                        transaction.status.toLocaleUpperCase()
                                    "
                                    class="w-fit"
                                />
                            </div>
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
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-6">
                <template v-if="transactions.length > 0">
                    <div
                        v-for="(transaction, index) in transactions"
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
                <div v-else class="flex items-center justify-center h-[40vh]">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="props.transactions.total > 0"
                class="flex flex-col gap-2 mt-4"
            >
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ props.transactions.from }} -
                    {{ props.transactions.to }} dari
                    {{ props.transactions.total }} item
                </p>
                <DefaultPagination :links="props.transactions.links" />
            </div>

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

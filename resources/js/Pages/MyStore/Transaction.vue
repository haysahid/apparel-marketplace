<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { usePage, useForm, router, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminPagination from "@/Components/AdminPagination.vue";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import TextInput from "@/Components/TextInput.vue";
import Dropdown from "@/Components/Dropdown.vue";
import OrderStatusChip from "../Order/OrderStatusChip.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";

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
        <div>
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="bg-yellow-500 hover:bg-yellow-500/80 active:bg-yellow-500/90 focus:bg-yellow-500 focus:ring-yellow-500 max-sm:text-xs max-sm:px-4 max-sm:py-2 text-nowrap"
                    @click="$inertia.visit(route('my-store.product.create'))"
                >
                    + Tambah Data
                </PrimaryButton>

                <div class="flex items-center gap-2">
                    <Dropdown
                        v-if="brands"
                        id="brand_id"
                        v-model="filters.brand_id"
                        :options="filteredBrands"
                        option-label="name"
                        option-value="id"
                        placeholder="Pilih Brand"
                        align="left"
                        required
                        :error="filters.errors.brand_id"
                        class="hidden max-w-48 sm:block"
                        @update:modelValue="filters.errors.brand_id = null"
                        @onOpen="isBrandDropdownOpen = true"
                        @onClose="isBrandDropdownOpen = false"
                    >
                        <template #trigger>
                            <TextInput
                                :modelValue="
                                    filters.brand_id && !isBrandDropdownOpen
                                        ? filters.brand?.name
                                        : brandSearch
                                "
                                @update:modelValue="
                                    filters.brand_id && !isBrandDropdownOpen
                                        ? null
                                        : (brandSearch = $event)
                                "
                                class="w-full"
                                textClass="text-sm sm:text-base"
                                :bgClass="
                                    !filters.brand_id ? 'bg-gray-100' : ''
                                "
                                placeholder="Pilih Brand"
                            >
                                <template #suffix>
                                    <button
                                        v-if="
                                            filters.brand_id &&
                                            !isBrandDropdownOpen
                                        "
                                        type="button"
                                        class="absolute p-[7px] text-gray-400 bg-white rounded-full top-1 right-1 hover:bg-gray-100 transition-all duration-300 ease-in-out"
                                        @click="
                                            filters.brand_id = null;
                                            filters.brand = null;
                                            brandSearch = null;

                                            getTransactions();
                                        "
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            class="size-4 sm:size-5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        v-else
                                        type="button"
                                        class="absolute p-2 top-1.5 right-1"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            class="size-4 fill-gray-400"
                                        >
                                            <path
                                                d="M18.6054 7.3997C18.4811 7.273 18.3335 7.17248 18.1709 7.10389C18.0084 7.0353 17.8342 7 17.6583 7C17.4823 7 17.3081 7.0353 17.1456 7.10389C16.9831 7.17248 16.8355 7.273 16.7112 7.3997L11.4988 12.7028L6.28648 7.3997C6.03529 7.14415 5.69462 7.00058 5.33939 7.00058C4.98416 7.00058 4.64348 7.14415 4.3923 7.3997C4.14111 7.65526 4 8.00186 4 8.36327C4 8.72468 4.14111 9.07129 4.3923 9.32684L10.5585 15.6003C10.6827 15.727 10.8304 15.8275 10.9929 15.8961C11.1554 15.9647 11.3296 16 11.5055 16C11.6815 16 11.8557 15.9647 12.0182 15.8961C12.1807 15.8275 12.3284 15.727 12.4526 15.6003L18.6188 9.32684C19.1293 8.80747 19.1293 7.93274 18.6054 7.3997Z"
                                            />
                                        </svg>
                                    </button>
                                </template>
                            </TextInput>
                        </template>
                        <template #content>
                            <ul class="overflow-y-auto max-h-60">
                                <li
                                    v-for="brand in filteredBrands"
                                    :key="brand.id"
                                    @click="
                                        isBrandDropdownOpen = false;

                                        filters.brand_id = brand.id;
                                        filters.brand = brand;

                                        getTransactions();
                                    "
                                    class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                                >
                                    {{ brand.name }}
                                </li>
                            </ul>
                        </template>
                    </Dropdown>
                    <TextInput
                        v-model="filters.search"
                        placeholder="Cari transaksi..."
                        bgClass="bg-gray-100"
                        textClass="text-sm sm:text-base"
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
            <div class="mt-4 overflow-x-auto rounded-t-lg sm:mt-6">
                <table class="table-default">
                    <thead>
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
                    </thead>
                    <tbody>
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
                                    class="font-semibold text-center hover:text-primary"
                                >
                                    #{{ transaction.code }}
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
                                            (total, item) =>
                                                total + item.subtotal,
                                            0
                                        )
                                    )
                                }}
                            </td>
                            <td class="flex items-center justify-center">
                                <OrderStatusChip
                                    :status="transaction.status"
                                    :label="
                                        transaction.status.toLocaleUpperCase()
                                    "
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
                    </tbody>
                </table>

                <SuccessDialog
                    :show="showSuccessDialog"
                    :title="successMessage"
                    @close="showSuccessDialog = false"
                />
            </div>

            <!-- Pagination -->
            <AdminPagination :links="props.transactions.links" />
        </div>
    </MyStoreLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, nextTick } from "vue";
import { usePage, useForm, router, Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import TextInput from "@/Components/TextInput.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import DefaultTable from "@/Components/DefaultTable.vue";
import { useScreenSize } from "@/plugins/screen-size";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import MyOrderCard from "./Order/MyOrderCard.vue";
import StatusChip from "@/Components/StatusChip.vue";
import CustomPageProps from "@/types/model/CustomPageProps";
import SearchInput from "@/Components/SearchInput.vue";
import { scrollToTop } from "@/plugins/helpers";

const screenSize = useScreenSize();

const props = defineProps({
    invoices: {
        type: Object as () => PaginationModel<InvoiceEntity>,
        required: true,
    },
    brands: {
        type: Array as () => BrandEntity[],
        required: true,
    },
});

const page = usePage<CustomPageProps>();

const invoices = ref<PaginationModel<InvoiceEntity>>({
    ...props.invoices,
    data: props.invoices.data.map((invoice) => ({
        ...invoice,
        showDeleteModal: false,
    })),
});

const closeDeleteOrderDialog = (invoice, result) => {
    if (invoice) {
        invoice.showDeleteModal = false;
        if (result) {
            openSuccessDialog("Data Berhasil Dihapus");
        }
    }
};

const deleteOrder = (invoice) => {
    if (invoice) {
        const form = useForm({});
        form.delete(
            route("my-store.order.destroy", {
                invoice: invoice,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeleteOrderDialog(invoice, true);
                    getOrders();
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
    brand_id: null,
    brand: null,
});

const getQueryParams = () => {
    filters.page = parseInt(route().params.page) || null;
    filters.search = route().params.search || null;
    filters.brand_id = parseInt(route().params.brand_id) || null;
    filters.brand =
        props.brands.find((brand) => brand.id === filters.brand_id) || null;
};
getQueryParams();

const queryParams = computed(() => {
    return {
        page: filters.page || undefined,
        search: filters.search || undefined,
        brand_id: filters.brand_id || undefined,
    };
});

function getOrders() {
    router.get(route("my-store.order"), queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            invoices.value = {
                ...page.props.invoices,
                data: page.props.invoices.data.map((invoice) => ({
                    ...invoice,
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
            "search-order"
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
    <MyStoreLayout title="Pesanan" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="$inertia.visit(route('my-store.order.create'))"
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
                                          (brand) => brand.id === option.value
                                      )
                                    : null;
                                filters.page = 1;
                                getOrders();
                            }
                        "
                        @search="brandSearch = $event"
                        @clear="
                            filters.brand_id = null;
                            filters.brand = null;
                            brandSearch = '';
                            filters.page = 1;
                            getOrders();
                        "
                    />
                    <SearchInput
                        id="search-order"
                        v-model="filters.search"
                        placeholder="Cari pesanan..."
                        class="max-w-48"
                        @search="
                            filters.page = 1;
                            getOrders();
                        "
                    />
                </div>
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="invoices.data.length === 0"
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
                        v-for="(invoice, index) in invoices.data"
                        :key="invoice.id"
                    >
                        <td>
                            {{
                                index +
                                1 +
                                (props.invoices.current_page - 1) *
                                    props.invoices.per_page
                            }}
                        </td>
                        <td>
                            {{ $formatDate(invoice.created_at) }}
                        </td>
                        <td>
                            <Link
                                :href="
                                    route('my-store.order.edit', {
                                        invoice: invoice,
                                    })
                                "
                                class="hover:underline"
                            >
                                {{ invoice.code }}
                            </Link>
                        </td>
                        <td class="!whitespace-normal">
                            {{ invoice.transaction.user.name }}
                        </td>
                        <td>
                            {{ invoice.transaction.items.length }}
                        </td>
                        <td>
                            {{ invoice.transaction.payment_method.name }}
                        </td>
                        <td>
                            {{ invoice.transaction.shipping_method.name }}
                        </td>
                        <td>
                            {{ $formatCurrency(invoice.amount) }}
                        </td>
                        <td>
                            <StatusChip
                                :status="invoice.status"
                                :label="invoice.status.toLocaleUpperCase()"
                                class="w-fit"
                            />
                        </td>
                        <td>
                            <AdminItemAction
                                @edit="
                                    $inertia.visit(
                                        route('my-store.order.edit', {
                                            invoice: invoice,
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
                <template v-if="invoices.data.length > 0">
                    <div
                        v-for="(invoice, index) in invoices.data"
                        :key="invoice.id"
                    >
                        <MyOrderCard
                            :invoice="invoice"
                            @edit="
                                $inertia.visit(
                                    route('my-store.order.edit', {
                                        invoice: invoice,
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
            <div v-if="invoices.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ invoices.from }} - {{ invoices.to }} dari
                    {{ invoices.total }} item
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="invoices.links"
                    @change="
                        (page) => {
                            filters.page = page;
                            getOrders();
                        }
                    "
                />
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

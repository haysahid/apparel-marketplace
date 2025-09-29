<script setup lang="ts">
import { ref, onMounted, nextTick, computed } from "vue";
import { usePage, useForm, Link } from "@inertiajs/vue3";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import TextInput from "@/Components/TextInput.vue";
import { router } from "@inertiajs/vue3";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultTable from "@/Components/DefaultTable.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import { useScreenSize } from "@/plugins/screen-size";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import InfoTooltip from "@/Components/InfoTooltip.vue";
import MyCustomerCard from "./Customer/MyCustomerCard.vue";
import { getImageUrl } from "@/plugins/helpers";
import CustomPageProps from "@/types/model/CustomPageProps";
import { scrollToTop } from "@/plugins/helpers";
import SearchInput from "@/Components/SearchInput.vue";

const screenSize = useScreenSize();

const props = defineProps({
    customers: Object as () => PaginationModel<UserEntity>,
});

const customers = ref<PaginationModel<UserEntity>>({
    ...props.customers,
    data: props.customers.data.map((customer) => ({
        ...customer,
        showDeleteModal: false,
    })),
});

const filters = useForm({
    page: null,
    search: null,
});

const getQueryParams = () => {
    filters.page = parseInt(route().params.page) || null;
    filters.search = route().params.search || null;
};
getQueryParams();

const queryParams = computed(() => {
    return {
        page: filters.page || undefined,
        search: filters.search || undefined,
    };
});

function getCustomers() {
    router.get(route("my-store.customer"), queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            customers.value = {
                ...props.customers,
                data: props.customers.data.map((customer) => ({
                    ...customer,
                    showDeleteModal: false,
                })),
            };
            scrollToTop({ id: "main-area" });
            setSearchFocus();
        },
    });
}

const selectedCustomer = ref(null);
const showDeleteCustomerDialog = ref(false);

const openDeleteCustomerDialog = (customer) => {
    if (customer) {
        selectedCustomer.value = customer;
        showDeleteCustomerDialog.value = true;
    }
};

const closeDeleteCustomerDialog = (result = false) => {
    showDeleteCustomerDialog.value = false;
    if (result) {
        selectedCustomer.value = null;
        openSuccessDialog("Data Berhasil Dihapus");
    }
};

const deleteCustomer = () => {
    if (selectedCustomer.value) {
        const form = useForm({});
        form.delete(
            route("my-store.brand.destroy", {
                brand: selectedCustomer.value,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeleteCustomerDialog(true);
                    getCustomers();
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

const page = usePage<CustomPageProps>();

function canEdit(customer) {
    return false;
    // return page.props.auth.is_admin;
}

function setSearchFocus() {
    nextTick(() => {
        const input = document.getElementById(
            "search-customer"
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
    <MyStoreLayout title="Pelanggan" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-end gap-4">
                <!-- <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="$inertia.visit(route('my-store.brand.create'))"
                >
                    Tambah
                </PrimaryButton> -->
                <SearchInput
                    id="search-customer"
                    v-model="filters.search"
                    placeholder="Cari pelanggan..."
                    class="max-w-48"
                    @search="
                        filters.page = 1;
                        getCustomers();
                    "
                />
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="customers.data.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Pelanggan</th>
                        <th>Email</th>
                        <th>No. HP</th>
                        <th>Jenis</th>
                        <th class="w-24">Aksi</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr
                        v-for="(customer, index) in customers.data"
                        :key="customer.id"
                    >
                        <td>
                            {{
                                index +
                                1 +
                                (props.customers.current_page - 1) *
                                    props.customers.per_page
                            }}
                        </td>
                        <td>
                            <Link
                                :href="
                                    route('my-store.customer.show', {
                                        customer: customer,
                                    })
                                "
                                class="flex items-center gap-3 group"
                            >
                                <div class="flex items-center gap-3">
                                    <img
                                        v-if="customer.avatar"
                                        :src="getImageUrl(customer.avatar)"
                                        alt="Foto Pelanggan"
                                        class="object-contain rounded-full size-8"
                                    />
                                    <svg
                                        v-else
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="44"
                                        height="44"
                                        viewBox="0 0 44 44"
                                        class="size-8 fill-gray-400"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M40.3333 22.0003C40.3333 32.1258 32.1255 40.3337 22 40.3337C11.8745 40.3337 3.66663 32.1258 3.66663 22.0003C3.66663 11.8748 11.8745 3.66699 22 3.66699C32.1255 3.66699 40.3333 11.8748 40.3333 22.0003ZM27.5 16.5003C27.5 17.959 26.9205 19.358 25.889 20.3894C24.8576 21.4209 23.4586 22.0003 22 22.0003C20.5413 22.0003 19.1423 21.4209 18.1109 20.3894C17.0794 19.358 16.5 17.959 16.5 16.5003C16.5 15.0416 17.0794 13.6427 18.1109 12.6112C19.1423 11.5798 20.5413 11.0003 22 11.0003C23.4586 11.0003 24.8576 11.5798 25.889 12.6112C26.9205 13.6427 27.5 15.0416 27.5 16.5003ZM22 37.5837C25.1465 37.5887 28.2201 36.6366 30.8128 34.8538C31.9201 34.093 32.3931 32.6447 31.7478 31.4658C30.415 29.022 27.665 27.5003 22 27.5003C16.335 27.5003 13.585 29.022 12.2503 31.4658C11.6068 32.6447 12.0798 34.093 13.1871 34.8538C15.7798 36.6366 18.8535 37.5887 22 37.5837Z"
                                        />
                                    </svg>
                                    <p class="group-hover:underline">
                                        {{ customer.name }}
                                    </p>
                                </div>
                            </Link>
                        </td>
                        <td class="!whitespace-normal">
                            {{ customer.email }}
                        </td>
                        <td class="!whitespace-normal">
                            {{ customer.phone }}
                        </td>
                        <td class="!whitespace-normal">
                            {{ customer.store_roles[0]?.name || "-" }}
                        </td>
                        <td>
                            <AdminItemAction
                                v-if="canEdit(customer)"
                                @edit="
                                    $inertia.visit(
                                        route('my-store.brand.edit', {
                                            brand: customer,
                                        })
                                    )
                                "
                                @delete="openDeleteCustomerDialog(customer)"
                            />
                            <InfoTooltip
                                v-if="!canEdit(customer)"
                                :id="`table-tooltip-hint-${customer.id}`"
                                text="Pelanggan tidak dapat diedit atau dihapus"
                            />
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <div
                    v-if="customers.data.length > 0"
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                >
                    <MyCustomerCard
                        v-for="(customer, index) in customers.data"
                        :key="customer.id"
                        :customer="customer"
                        @edit="
                            $inertia.visit(
                                route('my-store.brand.edit', {
                                    brand: customer,
                                })
                            )
                        "
                        @delete="openDeleteCustomerDialog(customer)"
                    />
                </div>
                <div v-else class="flex items-center justify-center py-10">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="customers.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ customers.from }} - {{ customers.to }} dari
                    {{ customers.total }} item
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="customers.links"
                    @change="
                        (page) => {
                            filters.page = page;
                            getCustomers();
                        }
                    "
                />
            </div>
        </DefaultCard>

        <DeleteConfirmationDialog
            :show="showDeleteCustomerDialog"
            :title="`Hapus Pelanggan <b>${selectedCustomer?.name}</b>?`"
            @close="closeDeleteCustomerDialog()"
            @delete="deleteCustomer()"
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

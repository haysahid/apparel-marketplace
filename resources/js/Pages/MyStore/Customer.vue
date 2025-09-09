<script setup>
import { ref, onMounted } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
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
import AdminItemCard from "@/Components/AdminItemCard.vue";
import InfoTooltip from "@/Components/InfoTooltip.vue";
import MyCustomerCard from "./Customer/MyCustomerCard.vue";

const screenSize = useScreenSize();

const props = defineProps({
    customers: null,
});

const customers = ref(
    props.customers.data.map((customer) => ({
        ...customer,
        avatar: customer.avatar ? "/storage/" + customer.avatar : null,
        showDeleteModal: false,
    }))
);

const filters = useForm({
    search: null,
});

const getQueryParams = () => {
    filters.search = route().params.search;
};
getQueryParams();

function getCustomers() {
    let queryParams = {};

    if (filters.search) queryParams.search = filters.search;

    router.get(route("my-store.customer"), queryParams, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            customers.value = props.customers.data.map((brand) => ({
                ...brand,
                avatar: brand.avatar ? "/storage/" + brand.avatar : null,
                showDeleteModal: false,
            }));
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

const closeDeleteCustomerDialog = (result) => {
    showDeleteCustomerDialog.value = false;
    if (result) {
        selectedCustomer.value = null;
        openSuccessDialog("Data Berhasil Dihapus");
        customers.value = customers.value.filter(
            (b) => b.id !== selectedCustomer.value?.id
        );
    }
};

const deleteCustomer = () => {
    if (selectedCustomer.value) {
        const form = useForm();
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

const page = usePage();

function canEdit(customer) {
    return false;
    // return page.props.auth.is_admin;
}

onMounted(() => {
    if (page.props.flash.success) {
        openSuccessDialog(page.props.flash.success);
    }
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
                <TextInput
                    v-model="filters.search"
                    placeholder="Cari pelanggan..."
                    class="max-w-48"
                    @keyup.enter="getCustomers()"
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
                :isEmpty="customers.length === 0"
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
                        v-for="(customer, index) in customers"
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
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="customer.avatar"
                                    :src="customer.avatar"
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
                                <p>{{ customer.name }}</p>
                            </div>
                        </td>
                        <td class="!whitespace-normal">
                            {{ customer.email }}
                        </td>
                        <td class="!whitespace-normal">
                            {{ customer.phone }}
                        </td>
                        <td class="!whitespace-normal">
                            {{ customer.role.name }}
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
            <div
                v-if="!screenSize.is('xl')"
                class="flex flex-col gap-3 mt-4"
                :class="{ 'min-h-auto h-[68vh]': customers.length == 0 }"
            >
                <div
                    v-if="customers.length > 0"
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                >
                    <MyCustomerCard
                        v-for="(customer, index) in customers"
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
                <div v-else class="flex items-center justify-center h-[90%]">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="props.customers.total > 0"
                class="flex flex-col gap-2 mt-4"
            >
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ props.customers.from }} -
                    {{ props.customers.to }} dari
                    {{ props.customers.total }} item
                </p>
                <DefaultPagination :links="props.customers.links" />
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

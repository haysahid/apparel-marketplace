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

const screenSize = useScreenSize();

const props = defineProps({
    units: null,
});

const units = ref(
    props.units.data.map((unit) => ({
        ...unit,
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

function getUnits() {
    let queryParams = {};

    if (filters.search) queryParams.search = filters.search;

    router.get(route("my-store.unit"), queryParams, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            units.value = props.units.data.map((unit) => ({
                ...unit,
                showDeleteModal: false,
            }));
        },
    });
}

const selectedUnit = ref(null);
const showDeleteUnitDialog = ref(false);

const openDeleteUnitDialog = (unit) => {
    if (unit) {
        selectedUnit.value = unit;
        showDeleteUnitDialog.value = true;
    }
};

const closeDeleteUnitDialog = (result) => {
    showDeleteUnitDialog.value = false;
    if (result) {
        selectedUnit.value = null;
        openSuccessDialog("Data Berhasil Dihapus");
        units.value = units.value.filter(
            (b) => b.id !== selectedUnit.value?.id
        );
    }
};

const deleteUnit = () => {
    if (selectedUnit.value) {
        const form = useForm();
        form.delete(
            route("my-store.unit.destroy", {
                unit: selectedUnit.value,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeleteUnitDialog(true);
                    getUnits();
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

function canEdit(unit) {
    return (
        page.props.auth.is_admin ||
        page.props.auth.user.stores.some((store) => store.id === unit.store_id)
    );
}

onMounted(() => {
    if (page.props.flash.success) {
        openSuccessDialog(page.props.flash.success);
    }
});
</script>

<template>
    <MyStoreLayout title="Satuan" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="$inertia.visit(route('my-store.unit.create'))"
                >
                    Tambah
                </PrimaryButton>
                <TextInput
                    v-model="filters.search"
                    placeholder="Cari satuan..."
                    class="max-w-48"
                    @keyup.enter="getUnits()"
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
                :isEmpty="units.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Nama Satuan</th>
                        <th>Deskripsi</th>
                        <th class="w-24">Aksi</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr v-for="(unit, index) in units" :key="unit.id">
                        <td>
                            {{
                                index +
                                1 +
                                (props.units.current_page - 1) *
                                    props.units.per_page
                            }}
                        </td>
                        <td>
                            {{ unit.name }}
                        </td>
                        <td class="!whitespace-normal">
                            {{ unit.description }}
                        </td>
                        <td>
                            <div class="flex items-center justify-start gap-2">
                                <AdminItemAction
                                    v-if="canEdit(unit)"
                                    @edit="
                                        $inertia.visit(
                                            route('my-store.unit.edit', {
                                                unit: unit,
                                            })
                                        )
                                    "
                                    @delete="openDeleteUnitDialog(unit)"
                                />
                                <InfoTooltip
                                    v-if="!canEdit(unit)"
                                    :id="`table-tooltip-hint-${unit.id}`"
                                    text="Satuan bawaan sistem"
                                />
                            </div>
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <div
                    v-if="units.length > 0"
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <AdminItemCard
                        v-for="(unit, index) in units"
                        :key="unit.id"
                        :name="unit.name"
                        :description="unit.description"
                        :showImage="false"
                        :hideActions="!canEdit(unit)"
                        disabledHint="Satuan bawaan sistem"
                        @edit="
                            $inertia.visit(
                                route('my-store.unit.edit', {
                                    unit: unit,
                                })
                            )
                        "
                        @delete="openDeleteUnitDialog(unit)"
                    >
                        <template #leading>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="26"
                                height="27"
                                viewBox="0 0 26 27"
                                class="fill-gray-500 size-6 shrink-0"
                            >
                                <path
                                    d="M5.41667 22.75C4.82083 22.75 4.31094 22.538 3.887 22.1141C3.46306 21.6901 3.25072 21.1799 3.25 20.5833V7.06876C3.25 6.81598 3.29081 6.57223 3.37242 6.33751C3.45403 6.10278 3.57572 5.88612 3.7375 5.68751L5.09167 4.03542C5.29028 3.78264 5.53836 3.58837 5.83592 3.45259C6.13347 3.31681 6.44511 3.24928 6.77083 3.25001H19.2292C19.5542 3.25001 19.8658 3.31789 20.1641 3.45367C20.4624 3.58945 20.7104 3.78337 20.9083 4.03542L22.2625 5.68751C22.425 5.88612 22.5471 6.10278 22.6287 6.33751C22.7103 6.57223 22.7507 6.81598 22.75 7.06876V20.5833C22.75 21.1792 22.538 21.6894 22.1141 22.1141C21.6901 22.5388 21.1799 22.7507 20.5833 22.75H5.41667ZM5.85 6.50001H20.15L19.2292 5.41667H6.77083L5.85 6.50001ZM17.3333 8.66667H8.66667V17.3333L13 15.1667L17.3333 17.3333V8.66667Z"
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
            <div v-if="props.units.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ props.units.from }} -
                    {{ props.units.to }} dari {{ props.units.total }} item
                </p>
                <DefaultPagination :links="props.units.links" />
            </div>
        </DefaultCard>

        <DeleteConfirmationDialog
            :show="showDeleteUnitDialog"
            :title="`Hapus Kategori <b>${selectedUnit?.name}</b>?`"
            @close="closeDeleteUnitDialog()"
            @delete="deleteUnit()"
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

<script setup lang="ts">
import { ref, onMounted, nextTick, computed } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import { router } from "@inertiajs/vue3";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultTable from "@/Components/DefaultTable.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import { useScreenSize } from "@/plugins/screen-size";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import AdminItemCard from "@/Components/AdminItemCard.vue";
import InfoTooltip from "@/Components/InfoTooltip.vue";
import CustomPageProps from "@/types/model/CustomPageProps";
import { scrollToTop } from "@/plugins/helpers";
import SearchInput from "@/Components/SearchInput.vue";

const screenSize = useScreenSize();

const props = defineProps({
    pointRules: Object as () => PaginationModel<PointRuleEntity>,
});

const pointRules = ref<PaginationModel<PointRuleEntity>>({
    ...props.pointRules,
    data: props.pointRules.data.map((pointRule) => ({
        ...pointRule,
        showDeleteModal: false,
    })),
});

const filters = useForm({
    page: null,
    search: null,
});

const getQueryParams = () => {
    filters.page = route().params.page || null;
    filters.search = route().params.search || null;
};
getQueryParams();

const queryParams = computed(() => {
    return {
        page: filters.page || undefined,
        search: filters.search || undefined,
    };
});

function getPointRules() {
    router.get(route("my-store.point-rule"), queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            pointRules.value = {
                ...props.pointRules,
                data: props.pointRules.data.map((pointRule) => ({
                    ...pointRule,
                    showDeleteModal: false,
                })),
            };
            scrollToTop({ id: "main-area" });
            setSearchFocus();
        },
    });
}

const selectedPointRule = ref(null);
const showDeletePointRuleDialog = ref(false);

const openDeletePointRuleDialog = (pointRule) => {
    if (pointRule) {
        selectedPointRule.value = pointRule;
        showDeletePointRuleDialog.value = true;
    }
};

const closeDeletePointRuleDialog = (result = false) => {
    showDeletePointRuleDialog.value = false;
    if (result) {
        selectedPointRule.value = null;
        openSuccessDialog("Data Berhasil Dihapus");
    }
};

const deletePointRule = () => {
    if (selectedPointRule.value) {
        const form = useForm({});
        form.delete(
            route("my-store.point-rule.destroy", {
                pointRule: selectedPointRule.value,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeletePointRuleDialog(true);
                    getPointRules();
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

function canEdit(pointRule) {
    return (
        page.props.auth.is_admin ||
        page.props.auth.user.stores.some(
            (store) => store.id === pointRule.store_id
        )
    );
}

function setSearchFocus() {
    nextTick(() => {
        const input = document.getElementById(
            "search-point-rule"
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
    <MyStoreLayout title="Aturan Poin" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="$inertia.visit(route('my-store.point-rule.create'))"
                >
                    Tambah
                </PrimaryButton>
                <SearchInput
                    id="search-point-rule"
                    v-model="filters.search"
                    placeholder="Cari aturan poin..."
                    class="max-w-48"
                    @search="
                        filters.page = 1;
                        getPointRules();
                    "
                />
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="pointRules.data.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Nama Aturan</th>
                        <th>Jenis</th>
                        <th>Poin</th>
                        <th>Deskripsi</th>
                        <th>Tgl. Berlaku</th>
                        <th>Tgl. Kadaluarsa</th>
                        <th class="w-24">Aksi</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr
                        v-for="(pointRule, index) in pointRules.data"
                        :key="pointRule.id"
                    >
                        <td>
                            {{
                                index +
                                1 +
                                (props.pointRules.current_page - 1) *
                                    props.pointRules.per_page
                            }}
                        </td>
                        <td>
                            {{ pointRule.name }}
                        </td>
                        <td>
                            <div
                                class="px-2 py-1 text-sm font-medium text-gray-700 bg-gray-100 rounded-md w-fit"
                            >
                                {{ pointRule.type.replaceAll("_", " ") }}
                            </div>
                        </td>
                        <td>+{{ pointRule.points_earned }}</td>
                        <td class="!whitespace-normal">
                            <p class="line-clamp-2">
                                {{ pointRule.description }}
                            </p>
                        </td>
                        <td>
                            {{
                                $formatDate(pointRule.valid_from, {
                                    dateStyle: "medium",
                                }) ?? "-"
                            }}
                        </td>
                        <td>
                            {{
                                $formatDate(pointRule.valid_until, {
                                    dateStyle: "medium",
                                }) ?? "-"
                            }}
                        </td>
                        <td>
                            <div class="flex items-center justify-start gap-2">
                                <AdminItemAction
                                    v-if="canEdit(pointRule)"
                                    @edit="
                                        $inertia.visit(
                                            route('my-store.point-rule.edit', {
                                                pointRule: pointRule,
                                            })
                                        )
                                    "
                                    @delete="
                                        openDeletePointRuleDialog(pointRule)
                                    "
                                />
                                <InfoTooltip
                                    v-if="!canEdit(pointRule)"
                                    :id="`table-tooltip-hint-${pointRule.id}`"
                                    text="Aturan bawaan sistem"
                                />
                            </div>
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <div
                    v-if="pointRules.data.length > 0"
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <AdminItemCard
                        v-for="(pointRule, index) in pointRules.data"
                        :key="pointRule.id"
                        :name="pointRule.name"
                        :description="pointRule.type.replaceAll('_', ' ')"
                        :showImage="false"
                        :hideActions="!canEdit(pointRule)"
                        disabledHint="Aturan bawaan sistem"
                        @edit="
                            $inertia.visit(
                                route('my-store.point-rule.edit', {
                                    pointRule: pointRule,
                                })
                            )
                        "
                        @delete="openDeletePointRuleDialog(pointRule)"
                    >
                        <template #leading>
                            <p
                                class="w-16 text-lg font-bold text-center text-yellow-600"
                            >
                                +{{ pointRule.points_earned }}
                            </p>
                        </template>
                    </AdminItemCard>
                </div>
                <div v-else class="flex items-center justify-center py-10">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pointRules.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ pointRules.from }} - {{ pointRules.to }} dari
                    {{ pointRules.total }} item
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="pointRules.links"
                    @change="
                        (page) => {
                            filters.page = page;
                            getPointRules();
                        }
                    "
                />
            </div>
        </DefaultCard>

        <DeleteConfirmationDialog
            :show="showDeletePointRuleDialog"
            :title="`Hapus Kategori <b>${selectedPointRule?.name}</b>?`"
            @close="closeDeletePointRuleDialog()"
            @delete="deletePointRule()"
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

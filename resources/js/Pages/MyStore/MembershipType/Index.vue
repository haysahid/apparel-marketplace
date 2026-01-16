<script setup lang="ts">
import { ref, onMounted, nextTick, computed } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminItemAction from "@/Components/AdminItemAction.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import { router } from "@inertiajs/vue3";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultTable from "@/Components/DefaultTable.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import { useScreenSize } from "@/plugins/screen-size";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import AdminItemCard from "@/Components/AdminItemCard.vue";
import InfoTooltip from "@/Components/InfoTooltip.vue";
import CustomPageProps from "@/types/model/CustomPageProps";
import SearchInput from "@/Components/SearchInput.vue";
import { scrollToTop } from "@/plugins/helpers";
import { useDialogStore } from "@/stores/dialog-store";
import MemberBadge from "@/Components/MemberBadge.vue";

const screenSize = useScreenSize();

const props = defineProps({
    membershipTypes: Object as () => PaginationModel<MembershipTypeEntity>,
});

const membershipTypes = ref<PaginationModel<MembershipTypeEntity>>({
    ...props.membershipTypes,
    data: props.membershipTypes.data.map((membershipType) => ({
        ...membershipType,
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

function getMembershipTypes() {
    router.get(route("my-store.membership-type.index"), queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            membershipTypes.value = {
                ...props.membershipTypes,
                data: props.membershipTypes.data.map((membershipType) => ({
                    ...membershipType,
                    showDeleteModal: false,
                })),
            };
            scrollToTop({ id: "main-area" });
            setSearchFocus();
        },
    });
}

const selectedMembershipType = ref(null);
const showDeleteMembershipTypeDialog = ref(false);

const openDeleteMembershipTypeDialog = (membershipType) => {
    if (membershipType) {
        selectedMembershipType.value = membershipType;
        showDeleteMembershipTypeDialog.value = true;
    }
};

const closeDeleteMembershipTypeDialog = (result = false) => {
    showDeleteMembershipTypeDialog.value = false;
    if (result) {
        selectedMembershipType.value = null;
        useDialogStore().openSuccessDialog("Data Berhasil Dihapus");
    }
};

const deleteMembershipType = () => {
    if (selectedMembershipType.value) {
        const form = useForm({});
        form.delete(
            route("my-store.membership-type.destroy", {
                membershipType: selectedMembershipType.value,
            }),
            {
                onError: (errors) => {
                    useDialogStore().openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeleteMembershipTypeDialog(true);
                    getMembershipTypes();
                },
            }
        );
    }
};

const page = usePage<CustomPageProps>();

function canEdit(membershipType) {
    return (
        page.props.auth.is_admin ||
        page.props.auth.user.stores.some(
            (store) => store.id === membershipType.store_id
        )
    );
}

function setSearchFocus() {
    nextTick(() => {
        const input = document.getElementById(
            "search-membershipType"
        ) as HTMLInputElement;
        input?.focus({ preventScroll: true });
    });
}

onMounted(() => {
    if (page.props.flash.success) {
        useDialogStore().openSuccessDialog(page.props.flash.success);
    }
    setSearchFocus();
});
</script>

<template>
    <MyStoreLayout title="Jenis Keanggotaan" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="
                        $inertia.visit(route('my-store.membership-type.create'))
                    "
                >
                    Tambah
                </PrimaryButton>
                <SearchInput
                    id="search-membershipType"
                    v-model="filters.search"
                    placeholder="Cari jenis..."
                    class="max-w-48"
                    @search="
                        filters.page = 1;
                        getMembershipTypes();
                    "
                />
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="membershipTypes.data.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Level</th>
                        <th>Nama</th>
                        <th>Grup</th>
                        <th>Diskon Item</th>
                        <th>Diskon Pengiriman</th>
                        <th>Deskripsi</th>
                        <th class="w-24">Aksi</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr
                        v-for="(membershipType, index) in membershipTypes.data"
                        :key="membershipType.id"
                    >
                        <td>
                            {{
                                index +
                                1 +
                                (props.membershipTypes.current_page - 1) *
                                    props.membershipTypes.per_page
                            }}
                        </td>
                        <td>
                            <MemberBadge :membershipType="membershipType" />
                        </td>
                        <td>
                            <div>
                                <span>{{ membershipType.name }}</span>
                                <template v-if="membershipType.alias">
                                    <span class="text-xs text-gray-500">
                                        -
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ membershipType.alias }}
                                    </span>
                                </template>
                            </div>
                        </td>
                        <td>
                            {{ membershipType.group }}
                        </td>
                        <td>{{ membershipType.item_discount_percentage }}%</td>
                        <td>
                            {{ membershipType.shipping_discount_percentage }}%
                        </td>
                        <td class="!whitespace-normal">
                            <p class="line-clamp-2">
                                {{ membershipType.description || "-" }}
                            </p>
                        </td>
                        <td>
                            <AdminItemAction
                                v-if="canEdit(membershipType)"
                                @edit="
                                    $inertia.visit(
                                        route('my-store.membership-type.edit', {
                                            membershipType: membershipType,
                                        })
                                    )
                                "
                                @delete="
                                    openDeleteMembershipTypeDialog(
                                        membershipType
                                    )
                                "
                            />
                            <InfoTooltip
                                v-if="!canEdit(membershipType)"
                                :id="`table-tooltip-hint-${membershipType.id}`"
                                text="MembershipType bawaan sistem"
                            />
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <div
                    v-if="membershipTypes.data.length > 0"
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                >
                    <AdminItemCard
                        v-for="(membershipType, index) in membershipTypes.data"
                        :key="membershipType.id"
                        :name="membershipType.name"
                        :description="membershipType.description"
                        :hideActions="!canEdit(membershipType)"
                        disabledHint="MembershipType bawaan sistem"
                        @edit="
                            $inertia.visit(
                                route('my-store.membership-type.edit', {
                                    membershipType: membershipType,
                                })
                            )
                        "
                        @delete="openDeleteMembershipTypeDialog(membershipType)"
                    />
                </div>
                <div v-else class="flex items-center justify-center py-10">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="membershipTypes.total > 0"
                class="flex flex-col gap-2 mt-4"
            >
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ membershipTypes.from }} -
                    {{ membershipTypes.to }} dari
                    {{ membershipTypes.total }} item
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="membershipTypes.links"
                    @change="
                        (page) => {
                            filters.page = page;
                            getMembershipTypes();
                        }
                    "
                />
            </div>
        </DefaultCard>

        <DeleteConfirmationDialog
            :show="showDeleteMembershipTypeDialog"
            :title="`Hapus MembershipType <b>${selectedMembershipType?.name}</b>?`"
            @close="closeDeleteMembershipTypeDialog()"
            @delete="deleteMembershipType()"
        />
    </MyStoreLayout>
</template>

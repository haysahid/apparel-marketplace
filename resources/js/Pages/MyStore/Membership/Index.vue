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
    memberships: Object as () => PaginationModel<MembershipEntity>,
});

const memberships = ref<PaginationModel<MembershipEntity>>({
    ...props.memberships,
    data: props.memberships.data.map((membership) => ({
        ...membership,
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

function getMemberships() {
    router.get(route("my-store.membership.index"), queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            memberships.value = {
                ...props.memberships,
                data: props.memberships.data.map((membership) => ({
                    ...membership,
                    showDeleteModal: false,
                })),
            };
            scrollToTop({ id: "main-area" });
            setSearchFocus();
        },
    });
}

const selectedMembership = ref(null);
const showDeleteMembershipDialog = ref(false);

const openDeleteMembershipDialog = (membership) => {
    if (membership) {
        selectedMembership.value = membership;
        showDeleteMembershipDialog.value = true;
    }
};

const closeDeleteMembershipDialog = (result = false) => {
    showDeleteMembershipDialog.value = false;
    if (result) {
        selectedMembership.value = null;
        useDialogStore().openSuccessDialog("Data Berhasil Dihapus");
    }
};

const deleteMembership = () => {
    if (selectedMembership.value) {
        const form = useForm({});
        form.delete(
            route("my-store.membership.destroy", {
                membership: selectedMembership.value,
            }),
            {
                onError: (errors) => {
                    useDialogStore().openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeleteMembershipDialog(true);
                    getMemberships();
                },
            }
        );
    }
};

const page = usePage<CustomPageProps>();

function canEdit(membership) {
    return (
        page.props.auth.is_admin ||
        page.props.auth.user.stores.some(
            (store) => store.id === membership.store_id
        )
    );
}

function setSearchFocus() {
    nextTick(() => {
        const input = document.getElementById(
            "search-membership"
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
                    @click="$inertia.visit(route('my-store.membership.create'))"
                >
                    Tambah
                </PrimaryButton>
                <SearchInput
                    id="search-membership"
                    v-model="filters.search"
                    placeholder="Cari jenis..."
                    class="max-w-48"
                    @search="
                        filters.page = 1;
                        getMemberships();
                    "
                />
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="memberships.data.length === 0"
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
                        v-for="(membership, index) in memberships.data"
                        :key="membership.id"
                    >
                        <td>
                            {{
                                index +
                                1 +
                                (props.memberships.current_page - 1) *
                                    props.memberships.per_page
                            }}
                        </td>
                        <td>
                            <MemberBadge :membership="membership" />
                        </td>
                        <td>
                            <div>
                                <span>{{ membership.name }}</span>
                                <template v-if="membership.alias">
                                    <span class="text-xs text-gray-500">
                                        -
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ membership.alias }}
                                    </span>
                                </template>
                            </div>
                        </td>
                        <td>
                            {{ membership.group }}
                        </td>
                        <td>{{ membership.item_discount_percentage }}%</td>
                        <td>{{ membership.shipping_discount_percentage }}%</td>
                        <td class="!whitespace-normal">
                            <p class="line-clamp-2">
                                {{ membership.description || "-" }}
                            </p>
                        </td>
                        <td>
                            <AdminItemAction
                                v-if="canEdit(membership)"
                                @edit="
                                    $inertia.visit(
                                        route('my-store.membership.edit', {
                                            membership: membership,
                                        })
                                    )
                                "
                                @delete="openDeleteMembershipDialog(membership)"
                            />
                            <InfoTooltip
                                v-if="!canEdit(membership)"
                                :id="`table-tooltip-hint-${membership.id}`"
                                text="Membership bawaan sistem"
                            />
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <div
                    v-if="memberships.data.length > 0"
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                >
                    <AdminItemCard
                        v-for="(membership, index) in memberships.data"
                        :key="membership.id"
                        :name="membership.name"
                        :description="membership.description"
                        :hideActions="!canEdit(membership)"
                        disabledHint="Membership bawaan sistem"
                        @edit="
                            $inertia.visit(
                                route('my-store.membership.edit', {
                                    membership: membership,
                                })
                            )
                        "
                        @delete="openDeleteMembershipDialog(membership)"
                    />
                </div>
                <div v-else class="flex items-center justify-center py-10">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="memberships.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ memberships.from }} -
                    {{ memberships.to }} dari {{ memberships.total }} item
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="memberships.links"
                    @change="
                        (page) => {
                            filters.page = page;
                            getMemberships();
                        }
                    "
                />
            </div>
        </DefaultCard>

        <DeleteConfirmationDialog
            :show="showDeleteMembershipDialog"
            :title="`Hapus Membership <b>${selectedMembership?.name}</b>?`"
            @close="closeDeleteMembershipDialog()"
            @delete="deleteMembership()"
        />
    </MyStoreLayout>
</template>

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
                            <div
                                class="flex items-center justify-center gap-1 px-2 py-1 text-xs font-semibold text-center rounded-full"
                                :style="{
                                    backgroundColor: membershipType.hex_code_bg,
                                    color: membershipType.hex_code_text,
                                }"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    class="inline-block size-4"
                                >
                                    <path
                                        d="M4.04913 5.80188L7.85413 9.14988L11.2441 4.97588C11.3391 4.85919 11.4591 4.76542 11.5953 4.70155C11.7315 4.63768 11.8803 4.60536 12.0307 4.60699C12.1811 4.60863 12.3293 4.64417 12.464 4.71099C12.5988 4.7778 12.7168 4.87415 12.8091 4.99288L16.0441 9.14888L19.9721 5.75288C20.1264 5.61979 20.3176 5.53699 20.5202 5.51556C20.7228 5.49413 20.9271 5.5351 21.1058 5.63298C21.2845 5.73085 21.429 5.88097 21.52 6.06324C21.6111 6.24551 21.6442 6.45123 21.6151 6.65288L20.1151 16.9999H3.92213L2.39913 6.69988C2.36888 6.49711 2.40168 6.28995 2.49308 6.10645C2.58449 5.92294 2.73009 5.77197 2.91015 5.67397C3.09022 5.57597 3.29606 5.53568 3.49979 5.55856C3.70351 5.58144 3.89529 5.66638 4.04913 5.80188ZM4.00013 17.9999H20.0001V18.9999C20.0001 19.2651 19.8948 19.5194 19.7072 19.707C19.5197 19.8945 19.2654 19.9999 19.0001 19.9999H5.00013C4.73492 19.9999 4.48056 19.8945 4.29303 19.707C4.10549 19.5194 4.00013 19.2651 4.00013 18.9999V17.9999Z"
                                        fill="currentColor"
                                    />
                                </svg>
                                <span>VIP {{ membershipType.level }}</span>
                            </div>
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

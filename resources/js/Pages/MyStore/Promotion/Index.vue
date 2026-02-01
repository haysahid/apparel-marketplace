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
import PromotionCard from "./PromotionCard.vue";
import { useImageViewerStore } from "@/stores/image-viewer-store";

const screenSize = useScreenSize();

const props = defineProps({
    promotions: Object as () => PaginationModel<PromotionEntity>,
});

const promotions = ref<PaginationModel<PromotionEntity>>({
    ...props.promotions,
    data: props.promotions.data.map((promotion) => ({
        ...promotion,
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

function getPromotions() {
    router.get(route("my-store.promotion.index"), queryParams.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            promotions.value = {
                ...props.promotions,
                data: props.promotions.data.map((promotion) => ({
                    ...promotion,
                    showDeleteModal: false,
                })),
            };
            scrollToTop({ id: "main-area" });
            setSearchFocus();
        },
    });
}

const selectedPromotion = ref<PromotionEntity | null>(null);
const showDeletePromotionDialog = ref(false);

const openDeletePromotionDialog = (promotion: PromotionEntity) => {
    if (promotion) {
        selectedPromotion.value = promotion;
        showDeletePromotionDialog.value = true;
    }
};

const closeDeletePromotionDialog = (result = false) => {
    showDeletePromotionDialog.value = false;
    if (result) {
        selectedPromotion.value = null;
        useDialogStore().openSuccessDialog("Data Berhasil Dihapus");
    }
};

const deletePromotion = () => {
    if (selectedPromotion.value) {
        const form = useForm({});
        form.delete(
            route("my-store.promotion.destroy", {
                promotion: selectedPromotion.value,
            }),
            {
                onError: (errors) => {
                    useDialogStore().openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeletePromotionDialog(true);
                    getPromotions();
                },
            },
        );
    }
};

const page = usePage<CustomPageProps>();

function canEdit(promotion: PromotionEntity) {
    return (
        page.props.auth.is_admin ||
        page.props.auth.user.stores.some(
            (store) => store.id === promotion.store_id,
        )
    );
}

function canDelete(promotion: PromotionEntity) {
    return (
        page.props.auth.is_admin ||
        page.props.auth.user.stores.some(
            (store) => store.id === promotion.store_id,
        )
    );
}

function setSearchFocus() {
    nextTick(() => {
        const input = document.getElementById(
            "search-promotion",
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
    <MyStoreLayout title="Promosi" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="$inertia.visit(route('my-store.promotion.create'))"
                >
                    Tambah
                </PrimaryButton>
                <SearchInput
                    id="search-promotion"
                    v-model="filters.search"
                    placeholder="Cari promosi..."
                    class="max-w-48"
                    @search="
                        filters.page = 1;
                        getPromotions();
                    "
                />
            </div>

            <!-- Table -->
            <DefaultTable
                v-if="screenSize.is('xl')"
                :isEmpty="promotions.data.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Tgl. Mulai</th>
                        <th>Tgl. Berakhir</th>
                        <th class="w-24">Aksi</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr
                        v-for="(promotion, index) in promotions.data"
                        :key="promotion.id"
                    >
                        <td>
                            {{
                                index +
                                1 +
                                (props.promotions.current_page - 1) *
                                    props.promotions.per_page
                            }}
                        </td>
                        <td>
                            <img
                                v-if="promotion.image"
                                :src="$getImageUrl(promotion.image)"
                                :alt="promotion.name"
                                class="object-contain h-[60px] rounded aspect-[3/2]"
                                @click="
                                    useImageViewerStore().selectedImage = {
                                        original_url: promotion.image,
                                    } as MediaEntity
                                "
                            />
                            <div
                                v-else
                                class="flex items-center justify-center h-[60px] bg-gray-100 rounded aspect-[3/2]"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    class="size-6 fill-gray-400"
                                >
                                    <path
                                        d="M5 21C4.45 21 3.97933 20.8043 3.588 20.413C3.19667 20.0217 3.00067 19.5507 3 19V5C3 4.45 3.196 3.97933 3.588 3.588C3.98 3.19667 4.45067 3.00067 5 3H19C19.55 3 20.021 3.196 20.413 3.588C20.805 3.98 21.0007 4.45067 21 5V19C21 19.55 20.8043 20.021 20.413 20.413C20.0217 20.805 19.5507 21.0007 19 21H5ZM6 17H18L14.25 12L11.25 16L9 13L6 17Z"
                                    />
                                </svg>
                            </div>
                        </td>
                        <td>
                            {{ promotion.name }}
                        </td>
                        <td class="!whitespace-normal">
                            <p class="line-clamp-2">
                                {{ promotion.description || "-" }}
                            </p>
                        </td>
                        <td>
                            {{ $formatDate(promotion.start_date) || "-" }}
                        </td>
                        <td>
                            {{ $formatDate(promotion.end_date) || "-" }}
                        </td>
                        <td>
                            <AdminItemAction
                                v-if="canEdit(promotion)"
                                @edit="
                                    $inertia.visit(
                                        route('my-store.promotion.edit', {
                                            promotion: promotion,
                                        }),
                                    )
                                "
                                @delete="openDeletePromotionDialog(promotion)"
                            />
                            <InfoTooltip
                                v-if="!canEdit(promotion)"
                                :id="`table-tooltip-hint-${promotion.id}`"
                                text="Promotion bawaan sistem"
                            />
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div v-if="!screenSize.is('xl')" class="flex flex-col gap-3 mt-4">
                <div
                    v-if="promotions.data.length > 0"
                    class="grid grid-cols-1 gap-3"
                >
                    <PromotionCard
                        v-for="(promotion, index) in promotions.data"
                        :key="promotion.id"
                        :promotion="promotion"
                        :hideEditButton="!canEdit(promotion)"
                        :hideDeleteButton="!canDelete(promotion)"
                        @edit="
                            $inertia.visit(
                                route('my-store.promotion.edit', {
                                    promotion: promotion,
                                }),
                            )
                        "
                        @delete="openDeletePromotionDialog(promotion)"
                        @click="
                            useImageViewerStore().selectedImage = {
                                original_url: promotion.image,
                            } as MediaEntity
                        "
                    />
                </div>
                <div v-else class="flex items-center justify-center py-10">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="promotions.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ promotions.from }} - {{ promotions.to }} dari
                    {{ promotions.total }} item
                </p>
                <DefaultPagination
                    :isApi="true"
                    :links="promotions.links"
                    @change="
                        (page) => {
                            filters.page = page;
                            getPromotions();
                        }
                    "
                />
            </div>
        </DefaultCard>

        <DeleteConfirmationDialog
            :show="showDeletePromotionDialog"
            :title="`Hapus Promotion <b>${selectedPromotion?.name}</b>?`"
            @close="closeDeletePromotionDialog()"
            @delete="deletePromotion()"
        />
    </MyStoreLayout>
</template>

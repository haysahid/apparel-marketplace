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
import MyStoreBrandCard from "./Brand/MyStoreBrandCard.vue";
import AdminItemCard from "@/Components/AdminItemCard.vue";
import InfoTooltip from "@/Components/InfoTooltip.vue";

const screenSize = useScreenSize();

const props = defineProps({
    sizes: null,
});

const sizes = ref(
    props.sizes.data.map((size) => ({
        ...size,
        image: size.image ? "/storage/" + size.image : null,
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

function getSizes() {
    let queryParams = {};

    if (filters.search) queryParams.search = filters.search;

    router.get(route("my-store.size"), queryParams, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getQueryParams();
            sizes.value = props.sizes.data.map((size) => ({
                ...size,
                image: size.image ? "/storage/" + size.image : null,
                showDeleteModal: false,
            }));
        },
    });
}

const selectedSize = ref(null);
const showDeleteSizeDialog = ref(false);

const openDeleteSizeDialog = (size) => {
    if (size) {
        selectedSize.value = size;
        showDeleteSizeDialog.value = true;
    }
};

const closeDeleteSizeDialog = (result) => {
    showDeleteSizeDialog.value = false;
    if (result) {
        selectedSize.value = null;
        openSuccessDialog("Data Berhasil Dihapus");
        sizes.value = sizes.value.filter(
            (b) => b.id !== selectedSize.value?.id
        );
    }
};

const deleteSize = () => {
    if (selectedSize.value) {
        const form = useForm();
        form.delete(
            route("my-store.size.destroy", {
                size: selectedSize.value,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onSuccess: () => {
                    closeDeleteSizeDialog(true);
                    getSizes();
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

function canEdit(size) {
    return (
        page.props.auth.user.is_admin ||
        page.props.auth.user.stores.some((store) => store.id === size.store_id)
    );
}

onMounted(() => {
    if (page.props.flash.success) {
        openSuccessDialog(page.props.flash.success);
    }
});
</script>

<template>
    <MyStoreLayout title="Ukuran" :showTitle="true">
        <DefaultCard :isMain="true">
            <div class="flex items-center justify-between gap-4">
                <PrimaryButton
                    type="button"
                    class="max-sm:text-sm max-sm:px-4 max-sm:py-2"
                    @click="$inertia.visit(route('my-store.size.create'))"
                >
                    Tambah
                </PrimaryButton>
                <TextInput
                    v-model="filters.search"
                    placeholder="Cari ukuran..."
                    textClass="text-sm sm:text-base"
                    class="max-w-48"
                    @keyup.enter="getSizes()"
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
                :isEmpty="sizes.length === 0"
                class="mt-6"
            >
                <template #thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Nama Ukuran</th>
                        <th class="w-24">Aksi</th>
                    </tr>
                </template>
                <template #tbody>
                    <tr v-for="(size, index) in sizes" :key="size.id">
                        <td>
                            {{
                                index +
                                1 +
                                (props.sizes.current_page - 1) *
                                    props.sizes.per_page
                            }}
                        </td>
                        <td>
                            {{ size.name }}
                        </td>
                        <td>
                            <div class="flex items-center justify-start gap-2">
                                <AdminItemAction
                                    v-if="canEdit(size)"
                                    @edit="
                                        $inertia.visit(
                                            route('my-store.size.edit', {
                                                size: size,
                                            })
                                        )
                                    "
                                    @delete="openDeleteSizeDialog(size)"
                                />
                                <InfoTooltip
                                    v-if="!canEdit(size)"
                                    :id="`table-tooltip-hint-${size.id}`"
                                    text="Ukuran bawaan sistem"
                                />
                            </div>
                        </td>
                    </tr>
                </template>
            </DefaultTable>

            <!-- Mobile View -->
            <div
                v-if="!screenSize.is('xl')"
                class="mt-4 min-h-[68vh] flex flex-col gap-3"
                :class="{ 'min-h-auto h-[68vh]': sizes.length == 0 }"
            >
                <div
                    v-if="sizes.length > 0"
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
                >
                    <AdminItemCard
                        v-for="(size, index) in sizes"
                        :key="size.id"
                        :name="size.name"
                        :showImage="false"
                        :hideActions="!canEdit(size)"
                        disabledHint="Ukuran bawaan sistem"
                        @edit="
                            $inertia.visit(
                                route('my-store.size.edit', {
                                    size: size,
                                })
                            )
                        "
                        @delete="openDeleteSizeDialog(size)"
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
                                    d="M2.1665 7.31242C2.1665 6.52229 2.48038 5.76453 3.03908 5.20583C3.59778 4.64713 4.35555 4.33325 5.14567 4.33325H20.854C21.6441 4.33325 22.4019 4.64713 22.9606 5.20583C23.5193 5.76453 23.8332 6.52229 23.8332 7.31242V18.6874C23.8332 19.0786 23.7561 19.466 23.6064 19.8275C23.4567 20.1889 23.2372 20.5174 22.9606 20.794C22.684 21.0706 22.3555 21.2901 21.9941 21.4398C21.6326 21.5895 21.2452 21.6666 20.854 21.6666H5.14567C4.75444 21.6666 4.36704 21.5895 4.00559 21.4398C3.64414 21.2901 3.31572 21.0706 3.03908 20.794C2.76244 20.5174 2.543 20.1889 2.39328 19.8275C2.24356 19.466 2.1665 19.0786 2.1665 18.6874V7.31242ZM18.1782 7.82159C18.0259 7.66917 17.8194 7.58344 17.604 7.58325H14.8957C14.6802 7.58325 14.4735 7.66885 14.3211 7.82123C14.1688 7.9736 14.0832 8.18026 14.0832 8.39575C14.0832 8.61124 14.1688 8.8179 14.3211 8.97028C14.4735 9.12265 14.6802 9.20825 14.8957 9.20825H15.6432L13.7798 11.0705C13.7 11.1449 13.636 11.2346 13.5916 11.3343C13.5472 11.4339 13.5233 11.5415 13.5214 11.6506C13.5194 11.7597 13.5395 11.8681 13.5804 11.9692C13.6212 12.0704 13.6821 12.1623 13.7592 12.2395C13.8364 12.3166 13.9283 12.3774 14.0294 12.4183C14.1306 12.4592 14.239 12.4792 14.3481 12.4773C14.4572 12.4754 14.5648 12.4515 14.6644 12.4071C14.7641 12.3627 14.8538 12.2987 14.9282 12.2188L16.7915 10.3566V11.1041C16.7915 11.3196 16.8771 11.5262 17.0295 11.6786C17.1819 11.831 17.3885 11.9166 17.604 11.9166C17.8195 11.9166 18.0262 11.831 18.1785 11.6786C18.3309 11.5262 18.4165 11.3196 18.4165 11.1041V8.39575C18.4163 8.18034 18.3306 7.97381 18.1782 7.82159ZM8.39567 18.4166H11.1051C11.3206 18.4166 11.5272 18.331 11.6796 18.1786C11.832 18.0262 11.9176 17.8196 11.9176 17.6041C11.9176 17.3886 11.832 17.1819 11.6796 17.0296C11.5272 16.8772 11.3206 16.7916 11.1051 16.7916H10.3576L12.2209 14.9283C12.3689 14.7749 12.4507 14.5697 12.4487 14.3566C12.4468 14.1436 12.3612 13.9398 12.2105 13.7893C12.0598 13.6387 11.8559 13.5533 11.6429 13.5516C11.4299 13.5498 11.2247 13.6318 11.0715 13.7799L9.20817 15.6411V14.8936C9.20817 14.6781 9.12257 14.4714 8.97019 14.3191C8.81782 14.1667 8.61116 14.0811 8.39567 14.0811C8.18018 14.0811 7.97352 14.1667 7.82115 14.3191C7.66877 14.4714 7.58317 14.6781 7.58317 14.8936V17.6019C7.58317 17.8174 7.66877 18.0241 7.82115 18.1764C7.97352 18.3288 8.18018 18.4144 8.39567 18.4144"
                                />
                            </svg>
                        </template>
                    </AdminItemCard>
                </div>
                <div v-else class="flex items-center justify-center h-[90%]">
                    <p class="text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="props.sizes.total > 0" class="flex flex-col gap-2 mt-4">
                <p class="text-xs text-gray-500 sm:text-sm">
                    Menampilkan {{ props.sizes.from }} -
                    {{ props.sizes.to }} dari {{ props.sizes.total }} item
                </p>
                <DefaultPagination :links="props.sizes.links" />
            </div>
        </DefaultCard>

        <DeleteConfirmationDialog
            :show="showDeleteSizeDialog"
            :title="`Hapus Kategori <b>${selectedSize?.name}</b>?`"
            @close="closeDeleteSizeDialog()"
            @delete="deleteSize()"
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

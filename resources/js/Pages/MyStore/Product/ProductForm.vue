<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from "vue";
import TextInput from "@/Components/TextInput.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { useDraggable } from "vue-draggable-plus";
import ProductLinkForm from "./ProductLinkForm.vue";
import DialogModal from "@/Components/DialogModal.vue";
import InputGroup from "@/Components/InputGroup.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import DropdownSearchInputMultiple from "@/Components/DropdownSearchInputMultiple.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import BrandForm from "../Brand/BrandForm.vue";
import CategoryForm from "../Category/CategoryForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import VariantList from "./VariantList.vue";
import TabButton from "@/Components/TabButton.vue";
import InfoTooltip from "@/Components/InfoTooltip.vue";
import { useProductFormStore } from "@/stores/product-form-store";
import MediaForm from "@/Components/MediaForm.vue";
import Modal from "@/Components/Modal.vue";
import MediaCard from "@/Components/MediaCard.vue";
import { useDialogStore } from "@/stores/dialog-store";
import { router } from "@inertiajs/vue3";
import useDebounce from "@/plugins/debounce";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import mediaService from "@/services/my-store/media-service";
import { useImageViewerStore } from "@/stores/image-viewer-store";

const props = defineProps({
    product: {
        type: Object as () => ProductEntity | null,
        default: null,
    },
});

const formStore = useProductFormStore();

const isDragging = ref(false);

const imagesContainer = ref(null);
const imagesRef = computed({
    get: () => formStore.form.images,
    set: (value) => {
        formStore.form.images = value;
    },
});
const draggable = useDraggable(imagesContainer, imagesRef, {
    animation: 150,
    filter(event, target, sortable) {
        return target.classList.contains("no-drag");
    },
    onStart: (event) => {
        isDragging.value = true;
        const item = event.item;
        item.style.opacity = "0.2";
    },
    onEnd: (event) => {
        isDragging.value = false;
        const item = event.item;
        item.style.opacity = "1";
    },
});

// const linksContainer = ref(null);
// const linksRef = computed(() => formStore.form.links);
// const draggableLinks = useDraggable(linksContainer, linksRef, {
//     animation: 150,
//     onStart: (event) => {
//         isDragging.value = true;
//         const item = event.item;
//         item.style.opacity = "0.2";
//     },
//     onEnd: (event) => {
//         isDragging.value = false;
//         const item = event.item;
//         item.style.opacity = "1";
//     },
// });

const showMediaFormModal = ref(false);

const dialogStore = useDialogStore();

const submitProduct = () => {
    if (formStore.validateProductForm()) {
        formStore.submitProduct({
            autoShowDialog: false,
            onSuccess: (response) => {
                formStore.clearNewProductForm();

                const newProduct = response.data.result;

                if (route().current() === "my-store.product.create") {
                    router.visit(
                        route("my-store.product.edit", { id: newProduct.id }) +
                            `?tab=1`,
                        {
                            preserveState: true,
                            preserveScroll: false,
                            onFinish: () => {
                                nextTick(() => {
                                    dialogStore.openSuccessDialog(
                                        "Informasi produk berhasil disimpan. Silakan tambahkan variasi produk.",
                                    );
                                });
                            },
                        },
                    );
                }

                if (route().current() === "my-store.product.edit") {
                    router.reload({
                        onFinish: () => {
                            nextTick(() => {
                                dialogStore.openSuccessDialog(
                                    "Informasi produk berhasil disimpan.",
                                );
                            });
                        },
                    });
                }
            },
        });
    }
};

const tabIndex = computed(() => {
    if (route().current() !== "my-store.product.edit") {
        return 0;
    }

    return route().params.tab ? parseInt(route().params.tab as string) : 0;
});

const skuPrefixDebounce = useDebounce();
const checkSkuPrefixStatus = ref(null);

const checkSkuPrefix = (skuPrefix: string | null) => {
    if (!skuPrefix) {
        checkSkuPrefixStatus.value = null;
        return;
    }

    skuPrefixDebounce(() => {
        formStore.checkSkuPrefixAvailability(skuPrefix, {
            onChangeStatus: (status) => {
                checkSkuPrefixStatus.value = status;
            },
        });
    }, 500);
};

onMounted(() => {
    if (props.product) {
        formStore.initializeForm(props.product).then(() => {
            if (formStore.form.sku_prefix) {
                checkSkuPrefix(formStore.form.sku_prefix);
            }
        });
    } else {
        formStore.initializeForm().then(() => {
            if (formStore.form.sku_prefix) {
                checkSkuPrefix(formStore.form.sku_prefix);
            }
        });
    }
});

const countImageUsedInVariants = (imageId: number | string) => {
    let count = 0;

    formStore.form.variants.forEach((variant) => {
        variant.images.forEach((image) => {
            if (image.media_id === imageId) {
                count++;
            }
        });
    });

    return count;
};
</script>

<template>
    <div class="flex flex-col items-start sm:gap-4">
        <DefaultCard :isMain="true" class="w-full !p-0">
            <div class="flex w-full gap-4 p-4 border-b border-gray-100 sm:p-6">
                <div class="w-full">
                    <h2 class="text-lg font-semibold">Produk & Variasi</h2>
                    <p class="text-sm text-gray-500">
                        Isi informasi produk dan variasi.
                    </p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- <SecondaryButton
                        v-if="props.product"
                        type="button"
                        @click="$inertia.visit(route('my-store.product.index'))"
                    >
                        Selesai
                    </SecondaryButton> -->
                    <SecondaryButton
                        type="button"
                        @click="$inertia.visit(route('my-store.product.index'))"
                    >
                        Kembali
                    </SecondaryButton>
                </div>
            </div>

            <div
                class="w-full min-h-[60vh] flex flex-col sm:flex-row items-start"
            >
                <!-- Tab Navigation -->
                <div
                    class="flex sm:flex-col items-start w-full sm:w-[300px] divide-x sm:divide-y divide-gray-100 border-b border-gray-100"
                >
                    <TabButton
                        v-for="(tab, index) in formStore.tabs"
                        :key="index"
                        :title="tab.title"
                        :subtitle="tab.subtitle"
                        :isActive="index == tabIndex"
                        :error="
                            index === 1 && formStore.form.errors.variants
                                ? formStore.form.errors.variants
                                : null
                        "
                        @click="
                            router.replace({
                                url: `/my-store/product/${props.product?.id}?tab=${index}`,
                            })
                        "
                    >
                        <template v-if="tab.icon" #leading>
                            <span
                                v-if="tab.icon"
                                v-html="tab.icon"
                                class="[&>svg]:fill-gray-500 group-hover:[&>svg]:fill-gray-600 [&>svg]:transition-all [&>svg]:duration-300 [&>svg]:ease-in-out [&>svg]:size-5"
                                :class="{
                                    '[&>svg]:!fill-primary': index == tabIndex,
                                }"
                            ></span>
                        </template>
                    </TabButton>
                </div>

                <!-- Tab Content -->
                <div
                    class="flex items-center justify-center w-full h-full p-4 border-l border-gray-100 sm:p-6"
                >
                    <!-- Tab 0 -->
                    <form
                        v-show="tabIndex == 0"
                        @submit.prevent="submitProduct"
                        class="w-full"
                    >
                        <div class="flex flex-col w-full gap-4">
                            <h2 class="font-semibold">Informasi Produk</h2>

                            <!-- Name -->
                            <InputGroup for="name" label="Nama Produk" required>
                                <TextInput
                                    id="name"
                                    v-model="formStore.form.name"
                                    type="text"
                                    placeholder="Masukkan Nama Produk"
                                    required
                                    :autofocus="true"
                                    :error="formStore.form.errors.name"
                                    @update:modelValue="
                                        formStore.form.errors.name = null
                                    "
                                />
                            </InputGroup>

                            <div class="flex gap-4">
                                <!-- Brand -->
                                <InputGroup for="brand_id" label="Brand">
                                    <DropdownSearchInput
                                        id="brand_id"
                                        :modelValue="
                                            formStore.form.brand_id
                                                ? {
                                                      label: formStore.form
                                                          .brand?.name,
                                                      value: formStore.form
                                                          .brand_id,
                                                  }
                                                : null
                                        "
                                        :options="
                                            formStore.filteredBrands.map(
                                                (brand) => ({
                                                    label: brand.name,
                                                    value: brand.id,
                                                }),
                                            )
                                        "
                                        placeholder="Pilih Brand"
                                        :error="formStore.form.errors.brand_id"
                                        @update:modelValue="
                                            (option) => {
                                                formStore.form.brand_id =
                                                    option?.value;
                                                formStore.form.brand = option
                                                    ? formStore.filteredBrands.find(
                                                          (brand) =>
                                                              brand.id ===
                                                              option.value,
                                                      )
                                                    : null;
                                            }
                                        "
                                        @search="formStore.brandSearch = $event"
                                        @clear="
                                            formStore.form.brand_id = null;
                                            formStore.form.brand = null;
                                            formStore.brandSearch = '';
                                        "
                                    >
                                        <template #optionHeader>
                                            <div
                                                class="flex items-center justify-between gap-2"
                                            >
                                                <p
                                                    class="text-sm font-semibold"
                                                >
                                                    Pilih Brand
                                                </p>
                                                <button
                                                    type="button"
                                                    class="text-sm text-blue-500 hover:underline"
                                                    @click="
                                                        formStore.showAddBrandForm = true
                                                    "
                                                >
                                                    Tambah
                                                </button>
                                            </div>
                                        </template>
                                    </DropdownSearchInput>
                                </InputGroup>

                                <!-- SKU Prefix -->
                                <InputGroup
                                    for="sku_prefix"
                                    label="SKU Prefix"
                                    required
                                >
                                    <TextInput
                                        id="sku_prefix"
                                        v-model="formStore.form.sku_prefix"
                                        type="text"
                                        placeholder="Masukkan SKU Prefix"
                                        required
                                        :error="
                                            formStore.form.errors.sku_prefix
                                        "
                                        @update:modelValue="
                                            formStore.form.errors.sku_prefix =
                                                null;

                                            checkSkuPrefix($event);
                                        "
                                    >
                                        <template
                                            #suffix
                                            v-if="
                                                checkSkuPrefixStatus ===
                                                'loading'
                                            "
                                        >
                                            <div
                                                class="absolute circular-loading-xs right-2"
                                            />
                                        </template>
                                    </TextInput>
                                    <template #suffix>
                                        <InfoTooltip
                                            id="sku-prefix-info"
                                            text="SKU Prefix digunakan untuk mengidentifikasi variasi produk turunan."
                                        />
                                    </template>
                                </InputGroup>
                            </div>

                            <!-- Categories -->
                            <InputGroup
                                for="categories"
                                label="Kategori Produk"
                            >
                                <DropdownSearchInputMultiple
                                    id="categories"
                                    :modelValue="
                                        formStore.form.categories?.map(
                                            (category) => ({
                                                label: category.name,
                                                value: category.id,
                                            }),
                                        )
                                    "
                                    :options="
                                        formStore.filteredCategories.map(
                                            (category) => ({
                                                label: category.name,
                                                value: category.id,
                                            }),
                                        )
                                    "
                                    placeholder="Cari Kategori"
                                    :error="formStore.form.errors.categories"
                                    @update:modelValue="
                                        (options) => {
                                            formStore.form.categories =
                                                options.map((option) =>
                                                    formStore.categories.find(
                                                        (category) =>
                                                            category.id ===
                                                            option.value,
                                                    ),
                                                );
                                        }
                                    "
                                    @search="formStore.categorySearch = $event"
                                    @clear="
                                        formStore.form.categories = null;
                                        formStore.categorySearch = '';
                                    "
                                >
                                    <template #optionHeader>
                                        <div
                                            class="flex items-center justify-between gap-2"
                                        >
                                            <p class="text-sm font-semibold">
                                                Pilih Kategori
                                            </p>
                                            <button
                                                type="button"
                                                class="text-sm text-blue-500 hover:underline"
                                                @click="
                                                    formStore.showAddCategoryForm = true
                                                "
                                            >
                                                Tambah
                                            </button>
                                        </div>
                                    </template>
                                </DropdownSearchInputMultiple>
                            </InputGroup>

                            <!-- Discount -->
                            <InputGroup for="discount" label="Diskon (%)">
                                <TextInput
                                    id="discount"
                                    v-model.number="formStore.form.discount"
                                    type="number"
                                    placeholder="Masukkan Diskon"
                                    required
                                    :error="formStore.form.errors.discount"
                                    @update:modelValue="
                                        formStore.form.errors.discount = null
                                    "
                                />
                            </InputGroup>

                            <!-- Description -->
                            <InputGroup
                                for="description"
                                label="Deskripsi Produk"
                            >
                                <TextAreaInput
                                    id="description"
                                    v-model="formStore.form.description"
                                    type="text"
                                    placeholder="Masukkan Deskripsi"
                                    class="block w-full mt-1"
                                    required
                                    :error="formStore.form.errors.description"
                                    @update:modelValue="
                                        formStore.form.errors.description = null
                                    "
                                />
                            </InputGroup>

                            <!-- Images -->
                            <InputGroup label="Gambar Produk">
                                <div
                                    ref="imagesContainer"
                                    class="grid w-full grid-cols-2 gap-2 lg:grid-cols-3 xl:grid-cols-4"
                                >
                                    <template
                                        v-for="(image, index) in formStore.form
                                            .images"
                                        :key="index"
                                    >
                                        <MediaCard
                                            :media="image"
                                            :isSelected="false"
                                            :showCheckbox="false"
                                            :showRemoveButton="true"
                                            :showName="false"
                                            :showSize="false"
                                            @remove="
                                                image.showDeleteDialog = true
                                            "
                                            @click="
                                                useImageViewerStore().selectedImage =
                                                    image
                                            "
                                        >
                                            <template #default>
                                                <DeleteConfirmationDialog
                                                    :show="
                                                        image.showDeleteDialog
                                                    "
                                                    title="Hapus Gambar Produk"
                                                    description="Apakah Anda yakin ingin menghapus gambar ini dari produk?"
                                                    @delete="
                                                        formStore.form.images.splice(
                                                            index,
                                                            1,
                                                        );
                                                        image.showDeleteDialog = false;

                                                        mediaService().deleteMedia(
                                                            image.id,
                                                            {
                                                                autoShowDialog: true,
                                                                onSuccess:
                                                                    () => {
                                                                        formStore.getVariants();
                                                                    },
                                                            },
                                                        );
                                                    "
                                                    @close="
                                                        image.showDeleteDialog = false
                                                    "
                                                >
                                                    <template #icon>
                                                        <img
                                                            v-if="
                                                                image.original_url
                                                            "
                                                            :src="
                                                                image.original_url
                                                            "
                                                            :alt="image.name"
                                                            class="object-contain w-full h-32 my-2 rounded-lg"
                                                        />
                                                    </template>
                                                    <template
                                                        #content
                                                        v-if="
                                                            image.showDeleteDialog
                                                        "
                                                    >
                                                        <div
                                                            v-if="
                                                                countImageUsedInVariants(
                                                                    image.id,
                                                                ) > 0
                                                            "
                                                            class="flex flex-col items-center w-full gap-0.5 px-4 py-2 my-4 text-sm rounded-lg bg-red-50"
                                                        >
                                                            <p
                                                                class="text-xs text-red-600"
                                                            >
                                                                Perhatian!
                                                            </p>
                                                            <p
                                                                class="text-gray-700"
                                                            >
                                                                {{
                                                                    countImageUsedInVariants(
                                                                        image.id,
                                                                    )
                                                                }}
                                                                variasi yang
                                                                menggunakan
                                                                gambar ini akan
                                                                terpengaruh.
                                                            </p>
                                                        </div>
                                                    </template>
                                                </DeleteConfirmationDialog>
                                            </template>
                                        </MediaCard>
                                    </template>

                                    <button
                                        type="button"
                                        class="relative overflow-hidden transition-all ease-in-out border rounded-lg cursor-pointer group hover:border-primary-light hover:ring-1 hover:ring-primary-light no-drag"
                                        @click.prevent="
                                            showMediaFormModal = true
                                        "
                                    >
                                        <div
                                            class="flex flex-col items-center justify-center w-full h-32 gap-2 p-4 text-gray-500 transition-all duration-300 ease-in-out rounded-lg bg-gray-50 group-hover:bg-primary/10 group-hover:text-primary group-hover:border-primary"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                class="size-8"
                                            >
                                                <path
                                                    d="M5 21C4.45 21 3.97933 20.8043 3.588 20.413C3.19667 20.0217 3.00067 19.5507 3 19V5C3 4.45 3.196 3.97934 3.588 3.588C3.98 3.19667 4.45067 3.00067 5 3H12C12.2833 3 12.521 3.096 12.713 3.288C12.905 3.48 13.0007 3.71734 13 4C12.9993 4.28267 12.9033 4.52034 12.712 4.713C12.5207 4.90567 12.2833 5.00134 12 5H5V19H19V12C19 11.7167 19.096 11.4793 19.288 11.288C19.48 11.0967 19.7173 11.0007 20 11C20.2827 10.9993 20.5203 11.0953 20.713 11.288C20.9057 11.4807 21.0013 11.718 21 12V19C21 19.55 20.8043 20.021 20.413 20.413C20.0217 20.805 19.5507 21.0007 19 21H5ZM6 17H18L14.25 12L11.25 16L9 13L6 17ZM17 7H16C15.7167 7 15.4793 6.904 15.288 6.712C15.0967 6.52 15.0007 6.28267 15 6C14.9993 5.71734 15.0953 5.48 15.288 5.288C15.4807 5.096 15.718 5 16 5H17V4C17 3.71667 17.096 3.47934 17.288 3.288C17.48 3.09667 17.7173 3.00067 18 3C18.2827 2.99934 18.5203 3.09534 18.713 3.288C18.9057 3.48067 19.0013 3.718 19 4V5H20C20.2833 5 20.521 5.096 20.713 5.288C20.905 5.48 21.0007 5.71734 21 6C20.9993 6.28267 20.9033 6.52034 20.712 6.713C20.5207 6.90567 20.2833 7.00134 20 7H19V8C19 8.28334 18.904 8.521 18.712 8.713C18.52 8.905 18.2827 9.00067 18 9C17.7173 8.99934 17.48 8.90334 17.288 8.712C17.096 8.52067 17 8.28334 17 8V7Z"
                                                    fill="currentColor"
                                                />
                                            </svg>

                                            <p class="text-sm text-center">
                                                Tambah Gambar
                                            </p>
                                        </div>
                                    </button>
                                </div>
                            </InputGroup>

                            <!-- Links -->
                            <!-- <div
                                class="flex flex-col items-start w-full gap-2 mt-2"
                            >
                                <div
                                    class="flex items-start justify-between w-full gap-4"
                                >
                                    <div>
                                        <h2 class="font-semibold">
                                            Tautan Produk
                                        </h2>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Tambahkan tautan untuk meningkatkan
                                            visibilitas produk.
                                        </p>
                                    </div>
                                    <SecondaryButton
                                        type="button"
                                        class="text-nowrap"
                                        @click="formStore.openAddLinkForm"
                                    >
                                        Tambah Tautan
                                    </SecondaryButton>
                                </div>
                                <div
                                    v-show="formStore.form.links.length > 0"
                                    ref="linksContainer"
                                    class="flex flex-col items-start w-full gap-2 mt-1.5"
                                >
                                    <div
                                        v-for="(link, index) in formStore.form
                                            .links"
                                        :key="index"
                                        class="w-full"
                                    >
                                        <LinkItem
                                            :name="link.name"
                                            :url="link.url"
                                            :icon="link.platform?.icon"
                                            :index="index"
                                            :drag="isDragging"
                                            :showDeleteButton="true"
                                            @click="link.showEditForm = true"
                                            @delete="
                                                formStore.form.links.splice(
                                                    index,
                                                    1,
                                                )
                                            "
                                        />
                                        <DialogModal
                                            :show="link.showEditForm"
                                            title="Tambah Tautan Produk"
                                            maxWidth="sm"
                                            @close="link.showEditForm = false"
                                        >
                                            <template #content>
                                                <ProductLinkForm
                                                    :link="link"
                                                    @submit="
                                                        formStore.form.links[
                                                            index
                                                        ] = $event
                                                    "
                                                    @close="
                                                        link.showEditForm = false
                                                    "
                                                />
                                            </template>
                                        </DialogModal>
                                    </div>
                                </div>
                            </div> -->

                            <!-- Navigate -->
                            <PrimaryButton type="submit" class="mt-4 w-fit">
                                Simpan
                            </PrimaryButton>
                        </div>
                    </form>

                    <!-- Tab 1 -->
                    <div
                        v-show="tabIndex == 1"
                        class="flex flex-col w-full gap-4"
                    >
                        <!-- Variants -->
                        <VariantList
                            :isEdit="props.product != null"
                            :product="
                                props.product
                                    ? {
                                          ...props.product,
                                          variants: undefined,
                                      }
                                    : null
                            "
                            :variants="formStore.form.variants"
                            @onCreated="
                                (message) => {
                                    router.reload({
                                        onFinish: () => {
                                            dialogStore.openSuccessDialog(
                                                message,
                                            );
                                            formStore.getVariants();
                                        },
                                    });
                                }
                            "
                            @onUpdated="
                                (message) => {
                                    router.reload({
                                        onFinish: () => {
                                            dialogStore.openSuccessDialog(
                                                message,
                                            );
                                            formStore.getVariants();
                                        },
                                    });
                                }
                            "
                            @onDelete="
                                (variant) => {
                                    if (
                                        props.product != null &&
                                        variant.id != null
                                    ) {
                                        formStore.deleteVariant(variant);
                                    } else {
                                        const index =
                                            formStore.form.variants.findIndex(
                                                (v) =>
                                                    v.motif === variant.motif &&
                                                    v.color_id ===
                                                        variant.color_id &&
                                                    v.size_id ===
                                                        variant.size_id,
                                            );
                                        formStore.form.variants.splice(
                                            index,
                                            1,
                                        );
                                        dialogStore.openSuccessDialog(
                                            'Varian produk berhasil dihapus.',
                                        );
                                    }
                                }
                            "
                        />
                    </div>
                </div>
            </div>
        </DefaultCard>
    </div>

    <DialogModal
        :show="formStore.showAddLinkForm"
        title="Tambah Tautan Produk"
        @close="formStore.showAddLinkForm = false"
        maxWidth="sm"
    >
        <template #content>
            <ProductLinkForm
                :link="null"
                @submit="formStore.form.links.push($event)"
                @close="formStore.showAddLinkForm = false"
            />
        </template>
    </DialogModal>

    <!-- Add Brand Modal -->
    <DialogModal
        :show="formStore.showAddBrandForm"
        @close="formStore.showAddBrandForm = false"
        maxWidth="sm"
    >
        <template #content>
            <div class="w-full">
                <h2
                    class="w-full mb-3 text-lg font-medium text-center text-gray-900"
                >
                    Tambah Brand
                </h2>
                <BrandForm
                    :isDialog="true"
                    @onSubmitted="
                        (brandName) => {
                            formStore.showAddBrandForm = false;
                            formStore.brands = $page.props.brands;

                            const newBrand = formStore.brands.find(
                                (brand) => brand.name === brandName,
                            );
                            formStore.form.brand_id = newBrand.id;
                            formStore.form.brand = newBrand;

                            dialogStore.openSuccessDialog(
                                'Brand berhasil ditambahkan.',
                            );
                        }
                    "
                    @close="formStore.showAddBrandForm = false"
                    class="w-full"
                />
            </div>
        </template>
    </DialogModal>

    <!-- Add Category Modal -->
    <DialogModal
        :show="formStore.showAddCategoryForm"
        @close="formStore.showAddCategoryForm = false"
        maxWidth="sm"
    >
        <template #content>
            <div class="w-full">
                <h2
                    class="w-full mb-3 text-lg font-medium text-center text-gray-900"
                >
                    Tambah Kategori
                </h2>
                <CategoryForm
                    :isDialog="true"
                    @onSubmitted="
                        (categoryName) => {
                            formStore.showAddCategoryForm = false;
                            formStore.categories = $page.props.categories;

                            const newCategory = formStore.categories.find(
                                (category) => category.name === categoryName,
                            );
                            formStore.form.categories.push(newCategory);

                            dialogStore.openSuccessDialog(
                                'Kategori berhasil ditambahkan.',
                            );
                        }
                    "
                    @close="formStore.showAddCategoryForm = false"
                    class="w-full"
                />
            </div>
        </template>
    </DialogModal>

    <Modal :show="showMediaFormModal" @close="showMediaFormModal = false">
        <MediaForm
            modelType="product"
            :modelId="props.product?.id"
            collectionName="product"
            @close="showMediaFormModal = false"
            @selectedMediaList="
                (selectedMediaList) => {
                    formStore.form.images = [
                        ...formStore.form.images,
                        ...selectedMediaList,
                    ];
                    showMediaFormModal = false;
                }
            "
        />
    </Modal>
</template>

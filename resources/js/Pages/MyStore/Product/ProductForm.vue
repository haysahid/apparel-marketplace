<script setup lang="ts">
import { ref, computed } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ImageInput from "@/Components/ImageInput.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import { useDraggable } from "vue-draggable-plus";
import ProductLinkForm from "./ProductLinkForm.vue";
import DialogModal from "@/Components/DialogModal.vue";
import LinkItem from "@/Components/LinkItem.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import InputGroup from "@/Components/InputGroup.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import DropdownSearchInputMultiple from "@/Components/DropdownSearchInputMultiple.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import BrandForm from "../Brand/BrandForm.vue";
import CategoryForm from "../Category/CategoryForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import VariantList from "./VariantList.vue";
import TabButton from "@/Components/TabButton.vue";
import axios from "axios";
import InfoTooltip from "@/Components/InfoTooltip.vue";
import cookieManager from "@/plugins/cookie-manager";
import { goBack } from "@/plugins/helpers";
import { useProductFormStore } from "@/stores/product-form-store";

const props = defineProps({
    product: {
        type: Object,
        default: null,
    },
});

const formStore = useProductFormStore();
</script>

<template>
    <form @submit.prevent="formStore.submit">
        <div class="flex flex-col items-start sm:gap-4">
            <DefaultCard :isMain="true" class="w-full !p-0">
                <div
                    class="flex w-full gap-4 p-4 border-b border-gray-100 sm:p-6"
                >
                    <div class="w-full">
                        <h2 class="text-lg font-semibold">Produk & Variasi</h2>
                        <p class="text-sm text-gray-500">
                            Isi informasi produk dan variasi.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <SecondaryButton type="button" @click="goBack()">
                            Kembali
                        </SecondaryButton>
                        <PrimaryButton type="submit"> Simpan </PrimaryButton>
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
                            :isActive="index == formStore.tabIndex"
                            @click="formStore.tabIndex = index"
                        >
                            <template v-if="tab.icon" #leading>
                                <span
                                    v-if="tab.icon"
                                    v-html="tab.icon"
                                    class="[&>svg]:fill-gray-500 group-hover:[&>svg]:fill-gray-600 [&>svg]:transition-all [&>svg]:duration-300 [&>svg]:ease-in-out [&>svg]:size-5"
                                    :class="{
                                        '[&>svg]:!fill-primary':
                                            index == formStore.tabIndex,
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
                        <div
                            v-if="formStore.tabIndex == 0"
                            class="flex flex-col w-full gap-4"
                        >
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
                                                null
                                        "
                                    />
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
                                    autocomplete="discount"
                                    :error="formStore.form.errors.discount"
                                    @update:modelValue="
                                        formStore.form.errors.discount = null
                                    "
                                />
                            </InputGroup>

                            <!-- Description -->
                            <InputGroup
                                id="description"
                                label="Deskripsi Produk"
                            >
                                <TextAreaInput
                                    id="description"
                                    v-model="formStore.form.description"
                                    type="text"
                                    placeholder="Masukkan Deskripsi"
                                    class="block w-full mt-1"
                                    required
                                    autocomplete="description"
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
                                    class="flex flex-wrap w-full gap-2"
                                >
                                    <ImageInput
                                        v-for="(image, index) in formStore.form
                                            .images"
                                        :key="image.id"
                                        :id="`image-${image.id}`"
                                        :modelValue="image.image"
                                        type="file"
                                        accept="image/*"
                                        placeholder="Upload gambar"
                                        class="!w-auto"
                                        width="!w-[100px]"
                                        height="h-[100px]"
                                        :showDeleteButton="true"
                                        :error="
                                            formStore.form.errors.images?.[
                                                index
                                            ]
                                        "
                                        :isDragging="formStore.drag"
                                        @update:modelValue="
                                            if (formStore.isNewImage(image)) {
                                                if (image.image == null) {
                                                    formStore.form.images.push({
                                                        id: `new-${
                                                            formStore.countNewImages +
                                                            1
                                                        }`,
                                                        image: null,
                                                    });
                                                }

                                                image.image = $event;
                                            } else {
                                                image.image = $event;
                                            }
                                        "
                                        @delete="
                                            if (formStore.isNewImage(image)) {
                                                formStore.form.images.splice(
                                                    index,
                                                    1,
                                                );
                                            } else {
                                                formStore.imagesToDelete.push(
                                                    image.id,
                                                );
                                                formStore.form.images.splice(
                                                    index,
                                                    1,
                                                );
                                            }
                                        "
                                    />
                                </div>
                            </InputGroup>

                            <!-- Links -->
                            <div
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
                                            :drag="formStore.drag"
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
                            </div>

                            <!-- Navigate -->
                            <PrimaryButton
                                type="button"
                                class="w-fit"
                                @click="
                                    if (formStore.validateProductForm()) {
                                        formStore.tabIndex = 1;
                                    }
                                "
                            >
                                Selanjutnya
                            </PrimaryButton>
                        </div>

                        <!-- Tab 1 -->
                        <div
                            v-if="formStore.tabIndex == 1"
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
                                @onAdd="
                                    (variant) => {
                                        formStore.form.variants.push(variant);
                                    }
                                "
                                @onAdded="
                                    (message) => {
                                        formStore.openSuccessDialog(message);
                                        formStore.getVariants();
                                    }
                                "
                                @onEdit="
                                    (variant) => {
                                        const index =
                                            formStore.form.variants.findIndex(
                                                (v) =>
                                                    v.motif === variant.motif &&
                                                    v.color_id ===
                                                        variant.color_id &&
                                                    v.size_id ===
                                                        variant.size_id,
                                            );
                                        if (index !== -1) {
                                            formStore.form.variants[index] = {
                                                ...variant,
                                                showEditForm: false,
                                            };
                                        }
                                    }
                                "
                                @onEditted="
                                    (message) => {
                                        formStore.openSuccessDialog(message);
                                        formStore.getVariants();
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
                                                        v.motif ===
                                                            variant.motif &&
                                                        v.color_id ===
                                                            variant.color_id &&
                                                        v.size_id ===
                                                            variant.size_id,
                                                );
                                            formStore.form.variants.splice(
                                                index,
                                                1,
                                            );
                                            formStore.openSuccessDialog(
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

                                formStore.openSuccessDialog(
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
                                    (category) =>
                                        category.name === categoryName,
                                );
                                formStore.form.categories.push(newCategory);

                                formStore.openSuccessDialog(
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

        <SuccessDialog
            :show="formStore.showSuccessDialog"
            :title="formStore.successMessage"
            @close="formStore.closeSuccessDialog"
        />

        <ErrorDialog
            :show="formStore.showErrorDialog"
            @close="formStore.closeErrorDialog"
        >
            <template #content>
                <div>
                    <div
                        class="mb-1 text-lg font-medium text-center text-gray-900"
                    >
                        Terjadi Kesalahan
                    </div>
                    <p class="text-center text-gray-700">
                        {{ formStore.errorMessage }}
                    </p>
                </div>
            </template>
        </ErrorDialog>
    </form>
</template>

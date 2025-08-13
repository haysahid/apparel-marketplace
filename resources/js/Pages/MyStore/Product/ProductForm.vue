<script setup>
import { ref, computed, onMounted } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ImageInput from "@/Components/ImageInput.vue";
import Dropdown from "@/Components/Dropdown.vue";
import Checkbox from "@/Components/Checkbox.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import { useDraggable } from "vue-draggable-plus";
import ProductLinkForm from "./ProductLinkForm.vue";
import DialogModal from "@/Components/DialogModal.vue";
import LinkItem from "@/Components/LinkItem.vue";
import VariantCard from "./VariantCard.vue";
import VariantForm from "./VariantForm.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import InputGroup from "@/Components/InputGroup.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import DropdownSearchInputMultiple from "@/Components/DropdownSearchInputMultiple.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import BrandForm from "../Brand/BrandForm.vue";
import CategoryForm from "../Category/CategoryForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import VariantList from "./VariantList.vue";

const props = defineProps({
    product: {
        type: Object,
        default: null,
    },
});

const form = useForm(
    props.product
        ? {
              ...props.product,
              categories: props.product?.categories || [],
              images: [
                  ...(props.product?.images?.map((image) => ({
                      ...image,
                      image: "/storage/" + image.image,
                  })) || []),
                  { id: "new-1", image: null },
              ],
              links: [
                  ...(props.product?.links?.map(function (link) {
                      if (!link.platform) return link;
                      return {
                          ...link,
                          platform: {
                              ...link.platform,
                              icon: "/storage/" + link.platform.icon,
                          },
                      };
                  }) || []),
              ],
              variants: [
                  ...(props.product?.variants?.map((variant) => ({
                      ...variant,
                      images:
                          variant.images?.map((image) => ({
                              ...image,
                              image: "/storage/" + image.image,
                          })) || [],
                  })) || []),
              ],
          }
        : {
              name: null,
              sku_prefix: null,
              brand_id: null,
              brand: null,
              discount: 0,
              description: null,
              categories: [],
              images: [{ id: "new-1", image: null }],
              links: [],
              variants: [],
          }
);

const drag = ref(false);

const page = usePage();

const brands = ref(page.props.brands || []);
const brandSearch = ref("");
const filteredBrands = computed(() => {
    return brands.value.filter((brand) =>
        brand.name.toLowerCase().includes(brandSearch.value.toLowerCase())
    );
});

const categories = ref(page.props.categories || []);
const categorySearch = ref("");
const filteredCategories = computed(() => {
    return categories.value.filter((category) =>
        category.name.toLowerCase().includes(categorySearch.value.toLowerCase())
    );
});

function uploadNewImage(image, index) {
    const token = `Bearer ${localStorage.getItem("access_token")}`;

    const formData = new FormData();
    formData.append("product_id", props.product.id);
    formData.append("image", image.image);
    formData.append("order", index);

    axios
        .post(`${page.props.ziggy.url}/api/my-store/product-image`, formData, {
            headers: {
                "Content-Type": "multipart/form-data",
                Authorization: token,
            },
        })
        .then((response) => {
            form.images[index] = {
                ...response.data.result,
                image: "/storage/" + response.data.result.image,
            };
        })
        .catch((error) => {
            if (error.response?.data?.error) {
                openErrorDialog(error.response.data.error);
            }
        });
}

function updateImage(index, image) {
    if (typeof image.image === "string" && image.order == index) {
        return;
    }

    const token = `Bearer ${localStorage.getItem("access_token")}`;

    const formData = new FormData();
    formData.append("_method", "PUT");
    if (image.image instanceof File) {
        formData.append("image", image.image);
    }
    formData.append("order", index);

    axios
        .post(
            `${page.props.ziggy.url}/api/my-store/product-image/${image.id}`,
            formData,
            {
                headers: {
                    "Content-Type": "multipart/form-data",
                    Authorization: token,
                },
            }
        )
        .then((response) => {
            form.images[index] = {
                ...response.data.result,
                image: "/storage/" + response.data.result.image,
            };
        })
        .catch((error) => {
            if (error.response?.data?.error) {
                openErrorDialog(error.response.data.error);
            }
        });
}

function updateImages() {
    const images = form.images || [];

    images.forEach((image, index) => {
        if (isNewImage(image) && image.image instanceof File) {
            uploadNewImage(image, index);
        } else if (isExistingImage(image)) {
            updateImage(index, image);
        }
    });
}

function deleteImages() {
    const token = `Bearer ${localStorage.getItem("access_token")}`;
    const images = imagesToDelete.value || [];

    images.forEach((imageId) => {
        axios
            .delete(
                `${page.props.ziggy.url}/api/my-store/product-image/${imageId}`,
                {
                    headers: {
                        Authorization: token,
                    },
                }
            )
            .then(() => {
                imagesToDelete.value = imagesToDelete.value.filter(
                    (id) => id !== imageId
                );
            })
            .catch((error) => {
                if (error.response?.data?.error) {
                    openErrorDialog(error.response.data.error);
                }
            });
    });
}

function deleteVariant(variant) {
    const token = `Bearer ${localStorage.getItem("access_token")}`;

    axios
        .delete(
            `${page.props.ziggy.url}/api/my-store/product-variant/${variant.id}`,
            {
                headers: {
                    Authorization: token,
                },
            }
        )
        .then((response) => {
            openSuccessDialog(response.data.meta.message);
            getVariants();
        })
        .catch((error) => {
            if (error.response?.data?.error) {
                openErrorDialog(error.response.data.error);
            }
        });
}

function getVariants() {
    const token = `Bearer ${localStorage.getItem("access_token")}`;

    axios
        .get(
            `${page.props.ziggy.url}/api/my-store/product/${props.product.id}`,
            {
                headers: {
                    Authorization: token,
                },
            }
        )
        .then((response) => {
            const product = response.data.result;
            form.variants = product.variants.map((variant) => ({
                ...variant,
                images:
                    variant.images?.map((image) => ({
                        ...image,
                        image: "/storage/" + image.image,
                    })) || [],
            }));
        })
        .catch((error) => {
            if (error.response?.data?.error) {
                openErrorDialog(error.response.data.error);
            }
        });
}

const submit = () => {
    if (props.product?.id) {
        updateImages();
        deleteImages();

        form.transform((data) => {
            const formData = new FormData();
            Object.keys(data).forEach((key) => {
                if (key === "images") return;

                if (key === "categories") {
                    data.categories.forEach((category, index) => {
                        formData.append(`categories[${index}]`, category.id);
                    });
                } else if (key === "links") {
                    data.links.forEach((link, index) => {
                        if (link.platform_id) {
                            formData.append(
                                `links[${index}][platform_id]`,
                                link.platform_id
                            );
                        }

                        if (link.url) {
                            formData.append(`links[${index}][url]`, link.url);
                        }
                    });
                } else if (data[key] !== null && data[key] !== undefined) {
                    formData.append(key, data[key]);
                }
            });
            return formData;
        }).post(route("my-store.product.update", props.product), {
            onError: (errors) => {
                console.error(errors);
                if (errors.error) {
                    openErrorDialog(errors.error);
                }
            },
        });
    } else {
        form.transform((data) => {
            const formData = new FormData();
            Object.keys(data).forEach((key) => {
                if (key === "images") {
                    data[key].forEach((image, index) => {
                        if (image.image instanceof File) {
                            formData.append(`images[${index}]`, image.image);
                        }
                    });
                } else if (key === "categories") {
                    data.categories.forEach((category, index) => {
                        formData.append(`categories[${index}]`, category.id);
                    });
                } else if (key === "links") {
                    data.links.forEach((link, index) => {
                        formData.append(
                            `links[${index}][platform_id]`,
                            link.platform_id
                        );
                        formData.append(`links[${index}][url]`, link.url);
                    });
                } else if (key === "variants") {
                    data.variants.forEach((variant, index) => {
                        formData.append(
                            `variants[${index}][motif]`,
                            variant.motif
                        );
                        formData.append(
                            `variants[${index}][color_id]`,
                            variant.color_id
                        );
                        formData.append(
                            `variants[${index}][size_id]`,
                            variant.size_id
                        );
                        formData.append(
                            `variants[${index}][material]`,
                            variant.material
                        );
                        formData.append(
                            `variants[${index}][base_selling_price]`,
                            variant.base_selling_price
                        );
                        formData.append(
                            `variants[${index}][discount]`,
                            variant.discount
                        );
                        formData.append(
                            `variants[${index}][current_stock_level]`,
                            variant.current_stock_level
                        );
                        formData.append(
                            `variants[${index}][unit_id]`,
                            variant.unit_id
                        );
                        variant.images.forEach((image, imgIndex) => {
                            console.log("variant image", image);
                            if (image.image instanceof File) {
                                formData.append(
                                    `variants[${index}][images][${imgIndex}]`,
                                    image.image
                                );
                            }
                        });
                    });
                } else if (data[key] !== null && data[key] !== undefined) {
                    formData.append(key, data[key]);
                }
            });
            return formData;
        }).post(route("my-store.product.store"), {
            onError: (errors) => {
                console.error(errors);
                if (errors.error) {
                    openErrorDialog(errors.error);
                } else if (errors.variants) {
                    openErrorDialog(errors.variants);
                }
            },
        });
    }
};

const imagesContainer = ref(null);

const draggable = useDraggable(imagesContainer, form.images, {
    animation: 150,
    onStart: (event) => {
        drag.value = true;
        const item = event.item;
        item.style.opacity = "0.2";
    },
    onEnd: (event) => {
        drag.value = false;
        const item = event.item;
        item.style.opacity = "1";
    },
});

const countNewImages = computed(() => {
    return form.images.filter((image) => isNewImage(image)).length;
});

const isNewImage = (image) => {
    return typeof image.id == "string" && image.id.startsWith("new-");
};

const isExistingImage = (image) => {
    return typeof image.id == "number";
};

const imagesToDelete = ref([]);
const variantsToDelete = ref([]);

const showAddLinkForm = ref(false);
const openAddLinkForm = () => {
    showAddLinkForm.value = true;
};
const linksContainer = ref(null);
const draggableLinks = useDraggable(linksContainer, form.links, {
    animation: 150,
    onStart: (event) => {
        drag.value = true;
        const item = event.item;
        item.style.opacity = "0.2";
    },
    onEnd: (event) => {
        drag.value = false;
        const item = event.item;
        item.style.opacity = "1";
    },
});

const showAddBrandForm = ref(false);
const showAddCategoryForm = ref(false);

const showSuccessDialog = ref(false);
const successMessage = ref(null);

const openSuccessDialog = (message) => {
    successMessage.value = message;
    showSuccessDialog.value = true;
};

const closeSuccessDialog = () => {
    showSuccessDialog.value = false;
};

const showErrorDialog = ref(false);
const errorMessage = ref(null);

const openErrorDialog = (message) => {
    errorMessage.value = message;
    showErrorDialog.value = true;
};

const closeErrorDialog = () => {
    showErrorDialog.value = false;
    errorMessage.value = null;
};
</script>

<template>
    <form @submit.prevent="submit">
        <div class="flex flex-col items-start sm:gap-4">
            <DefaultCard :isMain="true" class="flex w-full gap-4">
                <div class="w-full">
                    <h2 class="text-lg font-semibold">Produk & Variasi</h2>
                    <p class="text-sm text-gray-500">
                        Isi informasi produk dan variasi.
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <SecondaryButton
                        type="button"
                        @click="$inertia.visit(route('my-store.product'))"
                    >
                        Kembali
                    </SecondaryButton>
                    <PrimaryButton type="submit"> Simpan </PrimaryButton>
                </div>
            </DefaultCard>

            <div class="flex flex-col items-start w-full gap-4 lg:flex-row">
                <div class="flex flex-col items-start w-full gap-4">
                    <DefaultCard
                        :isMain="true"
                        class="flex flex-col w-full max-w-3xl gap-4"
                    >
                        <h2 class="font-semibold">Informasi Produk</h2>

                        <!-- Name -->
                        <InputGroup id="name" label="Nama Produk">
                            <TextAreaInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Masukkan Nama Produk"
                                required
                                :rows="1"
                                :autofocus="true"
                                :error="form.errors.username"
                                @update:modelValue="form.errors.username = null"
                            />
                        </InputGroup>

                        <!-- SKU Prefix -->
                        <InputGroup id="sku_prefix" label="SKU Prefix">
                            <TextInput
                                id="sku_prefix"
                                v-model="form.sku_prefix"
                                type="text"
                                placeholder="Masukkan SKU Prefix"
                                required
                                :error="form.errors.sku_prefix"
                                @update:modelValue="
                                    form.errors.sku_prefix = null
                                "
                            />
                        </InputGroup>

                        <!-- Brand -->
                        <InputGroup id="brand_id" label="Brand">
                            <DropdownSearchInput
                                id="brand_id"
                                :modelValue="
                                    form.brand_id
                                        ? {
                                              label: form.brand?.name,
                                              value: form.brand_id,
                                          }
                                        : null
                                "
                                :options="
                                    filteredBrands.map((brand) => ({
                                        label: brand.name,
                                        value: brand.id,
                                    }))
                                "
                                placeholder="Pilih Brand"
                                :error="form.errors.brand_id"
                                @update:modelValue="
                                    (option) => {
                                        form.brand_id = option?.value;
                                        form.brand = option
                                            ? filteredBrands.find(
                                                  (brand) =>
                                                      brand.id === option.value
                                              )
                                            : null;
                                    }
                                "
                                @search="brandSearch = $event"
                                @clear="
                                    form.brand_id = null;
                                    form.brand = null;
                                    brandSearch = '';
                                "
                            >
                                <template #optionHeader>
                                    <div
                                        class="flex items-center justify-between gap-2"
                                    >
                                        <p class="font-semibold">Pilih Brand</p>
                                        <button
                                            type="button"
                                            class="text-sm text-blue-500 hover:underline"
                                            @click="showAddBrandForm = true"
                                        >
                                            Tambah
                                        </button>
                                    </div>
                                </template>
                            </DropdownSearchInput>
                        </InputGroup>

                        <!-- Discount -->
                        <InputGroup id="discount" label="Diskon (%)">
                            <TextInput
                                id="discount"
                                v-model.number="form.discount"
                                type="number"
                                placeholder="Masukkan Diskon"
                                required
                                autocomplete="discount"
                                :error="form.errors.discount"
                                @update:modelValue="form.errors.discount = null"
                            />
                        </InputGroup>

                        <!-- Categories -->
                        <InputGroup id="categories" label="Kategori Produk">
                            <DropdownSearchInputMultiple
                                id="categories"
                                :modelValue="
                                    form.categories?.map((category) => ({
                                        label: category.name,
                                        value: category.id,
                                    }))
                                "
                                :options="
                                    filteredCategories.map((category) => ({
                                        label: category.name,
                                        value: category.id,
                                    }))
                                "
                                placeholder="Cari Kategori"
                                :error="form.errors.categories"
                                @update:modelValue="
                                    (options) => {
                                        form.categories = options.map(
                                            (option) =>
                                                categories.find(
                                                    (category) =>
                                                        category.id ===
                                                        option.value
                                                )
                                        );
                                    }
                                "
                                @search="categorySearch = $event"
                                @clear="
                                    form.categories = null;
                                    categorySearch = '';
                                "
                            >
                                <template #optionHeader>
                                    <div
                                        class="flex items-center justify-between gap-2"
                                    >
                                        <p class="font-semibold">
                                            Pilih Kategori
                                        </p>
                                        <button
                                            type="button"
                                            class="text-sm text-blue-500 hover:underline"
                                            @click="showAddCategoryForm = true"
                                        >
                                            Tambah
                                        </button>
                                    </div>
                                </template>
                            </DropdownSearchInputMultiple>
                        </InputGroup>

                        <!-- Description -->
                        <InputGroup id="description" label="Deskripsi Produk">
                            <TextAreaInput
                                id="description"
                                v-model="form.description"
                                type="text"
                                placeholder="Masukkan Deskripsi"
                                class="block w-full mt-1"
                                required
                                autocomplete="description"
                                :error="form.errors.description"
                                @update:modelValue="
                                    form.errors.description = null
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
                                    v-for="(image, index) in form.images"
                                    :key="image.id"
                                    :id="`image-${image.id}`"
                                    :modelValue="image.image"
                                    type="file"
                                    accept="image/*"
                                    placeholder="Upload Produk"
                                    class="!w-auto"
                                    width="!w-[180px]"
                                    height="h-[120px]"
                                    :showDeleteButton="true"
                                    :error="form.errors.images?.[index]"
                                    :isDragging="drag"
                                    @update:modelValue="
                                        if (isNewImage(image)) {
                                            if (image.image == null) {
                                                form.images.push({
                                                    id: `new-${
                                                        countNewImages + 1
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
                                        if (isNewImage(image)) {
                                            form.images.splice(index, 1);
                                        } else {
                                            imagesToDelete.push(image.id);
                                            form.images.splice(index, 1);
                                        }
                                    "
                                />
                            </div>
                        </InputGroup>

                        <hr class="w-full my-2 border-gray-200" />

                        <!-- Links -->
                        <div class="flex flex-col items-start w-full gap-2">
                            <div
                                class="flex items-start justify-between w-full gap-4"
                            >
                                <div>
                                    <h2 class="font-semibold">Tautan Produk</h2>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Tambahkan tautan untuk meningkatkan
                                        visibilitas produk.
                                    </p>
                                </div>
                                <SecondaryButton
                                    type="button"
                                    class="text-nowrap"
                                    @click="openAddLinkForm"
                                >
                                    Tambah Tautan
                                </SecondaryButton>
                            </div>
                            <div
                                v-show="form.links.length > 0"
                                ref="linksContainer"
                                class="flex flex-col items-start w-full gap-2 mt-1.5"
                            >
                                <div
                                    v-for="(link, index) in form.links"
                                    :key="index"
                                    class="w-full"
                                >
                                    <LinkItem
                                        :name="link.name"
                                        :url="link.url"
                                        :icon="link.platform?.icon"
                                        :index="index"
                                        :drag="drag"
                                        :showDeleteButton="true"
                                        @click="link.showEditForm = true"
                                        @delete="form.links.splice(index, 1)"
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
                                                    form.links[index] = $event
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
                    </DefaultCard>
                </div>
                <div class="flex flex-col items-start w-full gap-4">
                    <DefaultCard
                        :isMain="true"
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
                            :variants="form.variants"
                            @onAdd="
                                (variant) => {
                                    form.variants.push(variant);
                                }
                            "
                            @onAdded="
                                (message) => {
                                    openSuccessDialog(message);
                                    getVariants();
                                }
                            "
                            @onEdit="
                                (variant) => {
                                    const index = form.variants.findIndex(
                                        (v) =>
                                            v.motif === variant.motif &&
                                            v.color_id === variant.color_id &&
                                            v.size_id === variant.size_id
                                    );
                                    if (index !== -1) {
                                        form.variants[index] = {
                                            ...variant,
                                            showEditForm: false,
                                        };
                                    }
                                }
                            "
                            @onEditted="
                                (message) => {
                                    openSuccessDialog(message);
                                    getVariants();
                                }
                            "
                            @onDelete="
                                (variant) => {
                                    if (
                                        props.product != null &&
                                        variant.id != null
                                    ) {
                                        deleteVariant(variant);
                                    } else {
                                        const index = form.variants.findIndex(
                                            (v) =>
                                                v.motif === variant.motif &&
                                                v.color_id ===
                                                    variant.color_id &&
                                                v.size_id === variant.size_id
                                        );
                                        form.variants.splice(index, 1);
                                        openSuccessDialog(
                                            'Varian produk berhasil dihapus.'
                                        );
                                    }
                                }
                            "
                        />
                    </DefaultCard>
                </div>
            </div>
        </div>

        <DialogModal
            :show="showAddLinkForm"
            title="Tambah Tautan Produk"
            @close="showAddLinkForm = false"
            maxWidth="sm"
        >
            <template #content>
                <ProductLinkForm
                    :link="null"
                    @submit="form.links.push($event)"
                    @close="showAddLinkForm = false"
                />
            </template>
        </DialogModal>

        <!-- Add Brand Modal -->
        <DialogModal
            :show="showAddBrandForm"
            @close="showAddBrandForm = false"
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
                                showAddBrandForm = false;
                                brands = $page.props.brands;

                                const newBrand = brands.find(
                                    (brand) => brand.name === brandName
                                );
                                form.brand_id = newBrand.id;
                                form.brand = newBrand;

                                openSuccessDialog(
                                    'Brand berhasil ditambahkan.'
                                );
                            }
                        "
                        @close="showAddBrandForm = false"
                        class="w-full"
                    />
                </div>
            </template>
        </DialogModal>

        <!-- Add Category Modal -->
        <DialogModal
            :show="showAddCategoryForm"
            @close="showAddCategoryForm = false"
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
                                showAddCategoryForm = false;
                                categories = $page.props.categories;

                                const newCategory = categories.find(
                                    (category) => category.name === categoryName
                                );
                                form.categories.push(newCategory);

                                openSuccessDialog(
                                    'Kategori berhasil ditambahkan.'
                                );
                            }
                        "
                        @close="showAddCategoryForm = false"
                        class="w-full"
                    />
                </div>
            </template>
        </DialogModal>

        <SuccessDialog
            :show="showSuccessDialog"
            :title="successMessage"
            @close="closeSuccessDialog"
        />

        <ErrorDialog :show="showErrorDialog" @close="closeErrorDialog">
            <template #content>
                <div>
                    <div
                        class="mb-1 text-lg font-medium text-center text-gray-900"
                    >
                        Terjadi Kesalahan
                    </div>
                    <p class="text-center text-gray-700">{{ errorMessage }}</p>
                </div>
            </template>
        </ErrorDialog>
    </form>
</template>

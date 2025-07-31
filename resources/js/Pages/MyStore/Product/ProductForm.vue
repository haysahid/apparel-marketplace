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

const brands = page.props.brands || [];
const brandSearch = ref("");
const filteredBrands = computed(() => {
    return brands.filter((brand) =>
        brand.name.toLowerCase().includes(brandSearch.value.toLowerCase())
    );
});

const categories = page.props.categories || [];
const categorySearch = ref("");
const filteredCategories = computed(() => {
    return categories.filter((category) =>
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
            variantsToDelete.value = variantsToDelete.value.filter(
                (id) => id !== variant.id
            );

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

const showAddVariantForm = ref(false);
const openAddVariantForm = () => {
    showAddVariantForm.value = true;
};
const variantsContainer = ref(null);
const draggableVariants = useDraggable(variantsContainer, form.variants, {
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

const showSuccessDialog = ref(false);
const successMessage = ref(null);

const openSuccessDialog = (message) => {
    successMessage.value = message;
    showSuccessDialog.value = true;
};

const closeSuccessDialog = () => {
    showSuccessDialog.value = false;
    successMessage.value = null;
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
    <form @submit.prevent="submit" class="max-w-3xl">
        <div class="flex flex-col items-start gap-4">
            <h2 class="text-lg font-semibold">Informasi Produk</h2>

            <!-- Name -->
            <InputGroup id="name" label="Nama Produk">
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="Masukkan Nama Produk"
                    required
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
                    @update:modelValue="form.errors.sku_prefix = null"
                />
            </InputGroup>

            <!-- Brand -->
            <InputGroup id="brand_id" label="Nama Brand">
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
                                      (brand) => brand.id === option.value
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
                />
            </InputGroup>

            <!-- Images -->
            <InputGroup label="Gambar Produk">
                <div ref="imagesContainer" class="flex flex-wrap w-full gap-2">
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
                                        id: `new-${countNewImages + 1}`,
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
                            form.categories = options.map((option) =>
                                categories.find(
                                    (category) => category.id === option.value
                                )
                            );
                        }
                    "
                    @search="categorySearch = $event"
                    @clear="
                        form.categories = null;
                        categorySearch = '';
                    "
                />
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
                    @update:modelValue="form.errors.description = null"
                />
            </InputGroup>

            <!-- Links -->
            <div class="flex flex-col items-start w-full gap-2 mt-4">
                <h2 class="text-lg font-semibold">Tautan Produk</h2>
                <div
                    ref="linksContainer"
                    class="flex flex-col items-start w-full gap-2"
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
                                    @submit="form.links[index] = $event"
                                    @close="link.showEditForm = false"
                                />
                            </template>
                        </DialogModal>
                    </div>
                </div>

                <PrimaryButton
                    type="button"
                    class="!px-3 !py-2 text-xs !text-orange-500 bg-yellow-50 hover:bg-yellow-100/80 active:bg-yellow-100/90 focus:bg-yellow-100 focus:ring-yellow-100 outline outline-orange-200 mt-0.5"
                    @click="openAddLinkForm"
                >
                    + Tambah Tautan Produk
                </PrimaryButton>
            </div>

            <!-- Variants -->
            <div class="flex flex-col items-start w-full gap-2 mt-4">
                <h2 class="text-lg font-semibold">
                    Variasi Produk ({{ form.variants.length }})
                </h2>
                <div
                    ref="variantsContainer"
                    class="grid w-full grid-cols-1 gap-2 lg:grid-cols-2"
                >
                    <div
                        v-for="(variant, index) in form.variants"
                        :key="index"
                        class="w-full"
                    >
                        <VariantCard
                            :name="`${variant.motif} - ${variant.color?.name} - ${variant.size?.name}`"
                            :variant="variant"
                            :index="index"
                            @click="variant.showEditForm = true"
                            @delete="
                                if (props.product) {
                                    variant.showDeleteConfirmation = true;
                                } else {
                                    form.variants.splice(index, 1);
                                    if (variant.id) {
                                        variantsToDelete.push(variant.id);
                                    }
                                }
                            "
                        />
                        <DialogModal
                            :show="variant.showEditForm"
                            title="Ubah Variasi Produk"
                            @close="variant.showEditForm = false"
                        >
                            <template #content>
                                <VariantForm
                                    :isEdit="props.product != null"
                                    :product="form.data()"
                                    :variant="variant"
                                    @submit="
                                        form.variants[index] = {
                                            ...$event,
                                            showEditForm: false,
                                        }
                                    "
                                    @close="variant.showEditForm = false"
                                    @submitted="
                                        variant.showEditForm = false;
                                        openSuccessDialog($event);
                                        getVariants();
                                    "
                                />
                            </template>
                        </DialogModal>
                        <DeleteConfirmationDialog
                            :title="`Hapus Varian Produk <b>${variant.name}</b>?`"
                            :show="variant.showDeleteConfirmation"
                            @close="variant.showDeleteConfirmation = false"
                            @delete="
                                variant.showDeleteConfirmation = false;
                                deleteVariant(variant);
                            "
                        />
                    </div>
                </div>

                <PrimaryButton
                    type="button"
                    class="!px-3 !py-2 text-xs !text-orange-500 bg-yellow-50 hover:bg-yellow-100/80 active:bg-yellow-100/90 focus:bg-yellow-100 focus:ring-yellow-100 outline outline-orange-200 mt-0.5"
                    @click="openAddVariantForm"
                >
                    + Tambah Variasi Produk
                </PrimaryButton>
            </div>

            <div class="flex items-center gap-4 mt-4">
                <PrimaryButton type="submit"> Simpan </PrimaryButton>
                <SecondaryButton
                    type="button"
                    @click="$inertia.visit(route('my-store.product'))"
                >
                    Kembali
                </SecondaryButton>
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

        <DialogModal
            :show="showAddVariantForm"
            title="Tambah Variasi Produk"
            @close="showAddVariantForm = false"
        >
            <template #content>
                <VariantForm
                    :isEdit="props.product != null"
                    :product="form.data()"
                    :variant="null"
                    @submit="form.variants.push($event)"
                    @close="showAddVariantForm = false"
                    @submitted="
                        showAddVariantForm = false;
                        openSuccessDialog($event);
                        getVariants();
                    "
                />
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

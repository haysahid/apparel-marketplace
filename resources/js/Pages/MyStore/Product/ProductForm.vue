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
import TabButton from "@/Components/TabButton.vue";

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

const tabs = computed(() => [
    {
        title: "Informasi Produk",
        icon: `
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="26"
                height="27"
                viewBox="0 0 26 27"
                class="fill-primary"
            >
                <rect opacity="0.01" y="0.695312" width="26" height="26"/>
                <path d="M12.1875 13.5111L3.25 9.75195V18.1261C3.25962 18.8391 3.68236 19.4817 4.33333 19.7728L12.1225 23.4453H12.1875V13.5111Z"/>
                <path d="M13 12.0701L22.2192 8.20256C22.064 8.03018 21.8762 7.89026 21.6667 7.79089L13.8667 4.14006C13.3181 3.8804 12.6819 3.8804 12.1333 4.14006L4.33332 7.79089C4.12376 7.89026 3.93598 8.03018 3.78082 8.20256L13 12.0701Z"/>
                <path d="M13.8125 13.5111V23.4453H13.8667L21.6667 19.7728C22.3141 19.4834 22.7362 18.846 22.75 18.137V9.75195L13.8125 13.5111Z"/>
            </svg>
            `,
    },
    {
        title: `Variasi Produk (${form.variants.length})`,
        icon: `
        <svg 
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            class="fill-primary"
        >
            <path d="M20.9549 8.80399V11.014C20.9549 11.2129 20.8759 11.4037 20.7353 11.5443C20.5946 11.685 20.4039 11.764 20.2049 11.764C20.006 11.764 19.8153 11.685 19.6746 11.5443C19.534 11.4037 19.4549 11.2129 19.4549 11.014V8.80399C19.4613 8.54462 19.4172 8.28649 19.3249 8.04399L12.0249 12.424V20.614C12.1398 20.5798 12.2505 20.5329 12.3549 20.474L14.8849 19.074C15.0572 18.986 15.2566 18.967 15.4424 19.0208C15.6282 19.0747 15.7865 19.1975 15.8849 19.364C15.9746 19.537 15.9935 19.7381 15.9375 19.9248C15.8815 20.1114 15.7551 20.2689 15.5849 20.364L13.0649 21.764C12.5118 22.0731 11.8886 22.2349 11.2549 22.234C10.6185 22.2322 9.99268 22.0706 9.43494 21.764L3.48494 18.464C2.90593 18.1356 2.42363 17.6604 2.08658 17.0864C1.74953 16.5123 1.56961 15.8597 1.56494 15.194V8.80399C1.56494 8.13499 1.74494 7.47899 2.08494 6.90399C2.14227 6.80066 2.20894 6.70399 2.28494 6.61399C2.59696 6.16685 3.00751 5.79734 3.48494 5.53399L9.48494 2.22399C10.041 1.92378 10.663 1.7666 11.2949 1.7666C11.9269 1.7666 12.5489 1.92378 13.1049 2.22399L19.1049 5.53399C19.5249 5.76499 19.8939 6.08199 20.1849 6.46399C20.2296 6.50999 20.2696 6.55999 20.3049 6.61399C20.3809 6.70399 20.4476 6.80066 20.5049 6.90399C20.8249 7.48399 20.9809 8.14099 20.9549 8.80399Z"/>
            <path d="M22.4351 16.4241C22.4351 16.623 22.3561 16.8138 22.2154 16.9544C22.0748 17.0951 21.884 17.1741 21.6851 17.1741H19.9251V18.9241C19.9251 19.123 19.8461 19.3138 19.7054 19.4544C19.5648 19.5951 19.374 19.6741 19.1751 19.6741C18.9762 19.6741 18.7854 19.5951 18.6447 19.4544C18.5041 19.3138 18.4251 19.123 18.4251 18.9241V17.1741H16.7051C16.5062 17.1741 16.3154 17.0951 16.1747 16.9544C16.0341 16.8138 15.9551 16.623 15.9551 16.4241C15.9551 16.2252 16.0341 16.0344 16.1747 15.8937C16.3154 15.7531 16.5062 15.6741 16.7051 15.6741H18.4451V13.9241C18.4451 13.7252 18.5241 13.5344 18.6647 13.3937C18.8054 13.2531 18.9962 13.1741 19.1951 13.1741C19.394 13.1741 19.5848 13.2531 19.7254 13.3937C19.8661 13.5344 19.9451 13.7252 19.9451 13.9241V15.6741H21.7051C21.8997 15.6817 22.0839 15.7638 22.2197 15.9034C22.3556 16.043 22.4327 16.2293 22.4351 16.4241Z"/>
        </svg>
        `,
    },
]);
const tabIndex = ref(0);
</script>

<template>
    <form @submit.prevent="submit">
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
                    <div class="flex items-center gap-4">
                        <SecondaryButton
                            type="button"
                            @click="$inertia.visit(route('my-store.product'))"
                        >
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
                            v-for="(tab, index) in tabs"
                            :key="index"
                            :title="tab.title"
                            :subtitle="tab.subtitle"
                            :isActive="index == tabIndex"
                            @click="tabIndex = index"
                        >
                            <template v-if="tab.icon" #leading>
                                <span
                                    v-if="tab.icon"
                                    v-html="tab.icon"
                                    class="[&>svg]:fill-gray-500 group-hover:[&>svg]:fill-gray-600 [&>svg]:transition-all [&>svg]:duration-300 [&>svg]:ease-in-out [&>svg]:size-5"
                                    :class="{
                                        '[&>svg]:!fill-primary':
                                            index == tabIndex,
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
                            v-if="tabIndex == 0"
                            class="flex flex-col w-full gap-4"
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
                                    @update:modelValue="
                                        form.errors.username = null
                                    "
                                />
                            </InputGroup>

                            <div class="flex gap-4">
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
                                                              brand.id ===
                                                              option.value
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
                                                <p
                                                    class="text-sm font-semibold"
                                                >
                                                    Pilih Brand
                                                </p>
                                                <button
                                                    type="button"
                                                    class="text-sm text-blue-500 hover:underline"
                                                    @click="
                                                        showAddBrandForm = true
                                                    "
                                                >
                                                    Tambah
                                                </button>
                                            </div>
                                        </template>
                                    </DropdownSearchInput>
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
                            </div>

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
                                            <p class="text-sm font-semibold">
                                                Pilih Kategori
                                            </p>
                                            <button
                                                type="button"
                                                class="text-sm text-blue-500 hover:underline"
                                                @click="
                                                    showAddCategoryForm = true
                                                "
                                            >
                                                Tambah
                                            </button>
                                        </div>
                                    </template>
                                </DropdownSearchInputMultiple>
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
                                    @update:modelValue="
                                        form.errors.discount = null
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
                                        placeholder="Upload gambar"
                                        class="!w-auto"
                                        width="!w-[100px]"
                                        height="h-[100px]"
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
                                            @delete="
                                                form.links.splice(index, 1)
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
                                                        form.links[index] =
                                                            $event
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
                                @click="tabIndex = 1"
                            >
                                Selanjutnya
                            </PrimaryButton>
                        </div>

                        <!-- Tab 1 -->
                        <div
                            v-if="tabIndex == 1"
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
                                                v.color_id ===
                                                    variant.color_id &&
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
                                            const index =
                                                form.variants.findIndex(
                                                    (v) =>
                                                        v.motif ===
                                                            variant.motif &&
                                                        v.color_id ===
                                                            variant.color_id &&
                                                        v.size_id ===
                                                            variant.size_id
                                                );
                                            form.variants.splice(index, 1);
                                            openSuccessDialog(
                                                'Varian produk berhasil dihapus.'
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

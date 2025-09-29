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
import { getImageUrl } from "@/plugins/helpers";
import cookieManager from "@/plugins/cookie-manager";

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
              images: [...props.product?.images, { id: "new-1", image: null }],
              links: props.product?.links || [],
              variants: props.product?.variants || [],
          }
        : {
              name: null,
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
const isBrandDropdownOpen = ref(false);

const filteredBrands = computed(() => {
    return brands.filter((brand) =>
        brand.name.toLowerCase().includes(brandSearch.value.toLowerCase())
    );
});

const categories = page.props.categories || [];

function uploadNewImage(image, index) {
    const token = `Bearer ${cookieManager.getItem("access_token")}`;

    const formData = new FormData();
    formData.append("product_id", props.product.id);
    formData.append("image", image.image);
    formData.append("order", index);

    axios
        .post(`${page.props.ziggy.url}/api/admin/product-image`, formData, {
            headers: {
                "Content-Type": "multipart/form-data",
                Authorization: token,
            },
        })
        .then((response) => {
            form.images[index] = response.data.result;
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

    const token = `Bearer ${cookieManager.getItem("access_token")}`;

    const formData = new FormData();
    formData.append("_method", "PUT");
    if (image.image instanceof File) {
        formData.append("image", image.image);
    }
    formData.append("order", index);

    axios
        .post(
            `${page.props.ziggy.url}/api/admin/product-image/${image.id}`,
            formData,
            {
                headers: {
                    "Content-Type": "multipart/form-data",
                    Authorization: token,
                },
            }
        )
        .then((response) => {
            form.images[index] = response.data.result;
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
    const token = `Bearer ${cookieManager.getItem("access_token")}`;
    const images = imagesToDelete.value || [];

    images.forEach((imageId) => {
        axios
            .delete(
                `${page.props.ziggy.url}/api/admin/product-image/${imageId}`,
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
        }).post(route("admin.product.update", props.product), {
            onError: (errors) => {
                console.error(errors);
                if (errors.error) {
                    openErrorDialog(errors.error);
                }
            },
            onFinish: () => {
                form.reset();
            },
        });
    } else {
        form.transform((data) => {
            const formData = new FormData();
            Object.keys(data).forEach((key) => {
                if (key === "images") {
                    data[key].forEach((image, index) => {
                        if (image.image instanceof File) {
                            formData.append(`images[${index}]`, image);
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
                } else if (data[key] !== null && data[key] !== undefined) {
                    formData.append(key, data[key]);
                }
            });
            return formData;
        }).post(route("admin.product.store"), {
            onError: (errors) => {
                if (errors.error) {
                    openErrorDialog(errors.error);
                }
            },
            onFinish: () => {
                form.reset();
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

const showErrorDialog = ref(false);
const errorMessage = ref(null);

const openErrorDialog = (message) => {
    errorMessage.value = message;
    showErrorDialog.value = true;
};
</script>

<template>
    <form @submit.prevent="submit" class="max-w-3xl">
        <div class="flex flex-col items-start gap-4">
            <h2 class="text-lg font-semibold">Informasi Produk</h2>

            <!-- Name -->
            <div
                class="flex flex-col w-full gap-y-1 gap-x-4 sm:items-center sm:flex-row"
            >
                <InputLabel
                    for="name"
                    value="Nama Produk"
                    class="w-[100px] sm:w-1/5 text-lg font-bold"
                />
                <span class="hidden text-sm sm:block">:</span>
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="Masukkan Nama Produk"
                    class="block w-full mt-1"
                    required
                    :autofocus="true"
                    :error="form.errors.username"
                    @update:modelValue="form.errors.username = null"
                />
            </div>

            <!-- Brand -->
            <div
                class="flex flex-col w-full gap-y-1 gap-x-4 sm:items-center sm:flex-row"
            >
                <InputLabel
                    for="brand_id"
                    value="Nama Brand"
                    class="text-lg font-bold sm:w-1/5"
                />
                <span class="hidden text-sm sm:block">:</span>
                <Dropdown
                    id="brand_id"
                    v-model="form.brand_id"
                    :options="form.brands"
                    option-label="name"
                    option-value="id"
                    placeholder="Pilih Brand"
                    align="left"
                    class="block w-full mt-1"
                    required
                    :error="form.errors.brand_id"
                    @update:modelValue="form.errors.brand_id = null"
                    @onOpen="isBrandDropdownOpen = true"
                    @onClose="isBrandDropdownOpen = false"
                >
                    <template #trigger>
                        <TextInput
                            :modelValue="
                                form.brand_id && !isBrandDropdownOpen
                                    ? form.brand.name
                                    : brandSearch
                            "
                            @update:modelValue="
                                form.brand_id && !isBrandDropdownOpen
                                    ? null
                                    : (brandSearch = $event)
                            "
                            class="w-full"
                            placeholder="Pilih Brand"
                        >
                            <template #suffix>
                                <button
                                    v-if="form.brand_id && !isBrandDropdownOpen"
                                    type="button"
                                    class="absolute p-[7px] text-gray-400 bg-white rounded-full top-1 right-1 hover:bg-gray-100 transition-all duration-300 ease-in-out"
                                    @click="
                                        form.brand_id = null;
                                        form.brand = null;
                                        brandSearch = '';
                                    "
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="size-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                                <button
                                    v-else
                                    type="button"
                                    class="absolute p-2 top-1.5 right-1"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        class="size-4 fill-gray-400"
                                    >
                                        <path
                                            d="M18.6054 7.3997C18.4811 7.273 18.3335 7.17248 18.1709 7.10389C18.0084 7.0353 17.8342 7 17.6583 7C17.4823 7 17.3081 7.0353 17.1456 7.10389C16.9831 7.17248 16.8355 7.273 16.7112 7.3997L11.4988 12.7028L6.28648 7.3997C6.03529 7.14415 5.69462 7.00058 5.33939 7.00058C4.98416 7.00058 4.64348 7.14415 4.3923 7.3997C4.14111 7.65526 4 8.00186 4 8.36327C4 8.72468 4.14111 9.07129 4.3923 9.32684L10.5585 15.6003C10.6827 15.727 10.8304 15.8275 10.9929 15.8961C11.1554 15.9647 11.3296 16 11.5055 16C11.6815 16 11.8557 15.9647 12.0182 15.8961C12.1807 15.8275 12.3284 15.727 12.4526 15.6003L18.6188 9.32684C19.1293 8.80747 19.1293 7.93274 18.6054 7.3997Z"
                                        />
                                    </svg>
                                </button>
                            </template>
                        </TextInput>
                    </template>
                    <template #content>
                        <ul class="overflow-y-auto max-h-60">
                            <li
                                v-for="brand in filteredBrands"
                                :key="brand.id"
                                @click="
                                    form.brand_id = brand.id;
                                    form.brand = brand;
                                "
                                class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                            >
                                {{ brand.name }}
                            </li>
                        </ul>
                    </template>
                </Dropdown>
            </div>

            <!-- Image -->
            <div
                class="flex flex-col w-full gap-y-1 gap-x-4 sm:items-start sm:flex-row"
            >
                <InputLabel
                    for="image"
                    value="Gambar Produk"
                    class="text-lg font-bold sm:w-1/5"
                />
                <span class="hidden text-sm sm:block">:</span>
                <div ref="imagesContainer" class="flex flex-wrap w-full gap-2">
                    <ImageInput
                        v-for="(image, index) in form.images"
                        :key="image.id"
                        :id="`image-${image.id}`"
                        v-model="image.image"
                        type="file"
                        accept="image/*"
                        placeholder="Upload Produk"
                        class="!w-auto mt-1"
                        width="!w-[180px]"
                        height="h-[120px]"
                        :showDeleteButton="true"
                        :error="form.errors.images?.[index]"
                        :isDragging="drag"
                        @update:modelValue="
                            if (isNewImage(image)) {
                                image.image = $event;
                                form.images.push({
                                    id: `new-${countNewImages + 1}`,
                                    image: null,
                                });
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
            </div>

            <!-- Discount -->
            <div
                class="flex flex-col w-full gap-y-1 gap-x-4 sm:items-center sm:flex-row"
            >
                <InputLabel
                    for="discount"
                    value="Diskon (%)"
                    class="text-lg font-bold sm:w-1/5"
                />
                <span class="hidden text-sm sm:block">:</span>
                <TextInput
                    id="discount"
                    v-model.number="form.discount"
                    type="number"
                    placeholder="Masukkan Diskon"
                    class="block w-full mt-1"
                    required
                    autocomplete="discount"
                    :error="form.errors.discount"
                    @update:modelValue="form.errors.discount = null"
                />
            </div>

            <!-- Categories -->
            <div
                class="flex flex-col w-full gap-y-1.5 gap-x-4 sm:items-start sm:flex-row"
            >
                <InputLabel
                    for="categories"
                    value="Kategori Produk"
                    class="text-lg font-bold sm:w-1/5"
                />
                <span class="hidden text-sm sm:block">:</span>
                <div class="flex flex-col items-start w-full gap-3 mt-[1px]">
                    <div
                        class="grid w-full grid-cols-1 gap-2 sm:gap-2.5 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <div
                            v-for="category in categories || []"
                            :key="category.id"
                            class="flex items-center justify-start"
                        >
                            <label
                                :for="`category-${category.id}`"
                                class="flex items-center gap-2 cursor-pointer [&>*]:cursor-pointer justify-start"
                            >
                                <Checkbox
                                    :id="`category-${category.id}`"
                                    :value="category.id.toString()"
                                    :label="category.name"
                                    :checked="
                                        form.categories
                                            .map((c) => c.id)
                                            .includes(category.id)
                                    "
                                    @update:checked="
                                        form.categories
                                            .map((c) => c.id)
                                            .includes(category.id)
                                            ? (form.categories =
                                                  form.categories.filter(
                                                      (c) =>
                                                          c.id !== category.id
                                                  ))
                                            : form.categories.push(category)
                                    "
                                />
                                <InputLabel
                                    :for="`category-${category.id}`"
                                    :value="category.name"
                                    class="text-sm text-gray-500"
                                />
                            </label>
                        </div>
                    </div>
                    <!-- <PrimaryButton
                        type="button"
                        class="!px-3 !py-2 text-xs !text-orange-500 bg-yellow-50 hover:bg-yellow-100/80 active:bg-yellow-100/90 focus:bg-yellow-100 focus:ring-yellow-100 outline outline-orange-200"
                        @click="
                            $inertia.visit(route('admin.certificate.create'))
                        "
                    >
                        + Tambah Kategori Produk
                    </PrimaryButton> -->
                </div>
            </div>

            <!-- Description -->
            <div class="flex flex-col items-start w-full gap-1 sm:gap-1.5">
                <InputLabel
                    for="description"
                    value="Deskripsi Produk"
                    class="text-lg font-bold"
                />
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
            </div>

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
                    class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2"
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
                                form.variants.splice(index, 1);
                                if (variant.id) {
                                    variantsToDelete.push(variant.id);
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
                                    :product="props.product"
                                    :variant="variant"
                                    @submit="
                                        form.variants[index] = {
                                            ...$event,
                                            showEditForm: false,
                                        }
                                    "
                                    @close="variant.showEditForm = false"
                                />
                            </template>
                        </DialogModal>
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

            <PrimaryButton type="submit" class="mt-4">
                Simpan Data
            </PrimaryButton>
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
                    :product="form"
                    :variant="null"
                    @submit="form.variants.push($event)"
                    @close="showAddVariantForm = false"
                />
            </template>
        </DialogModal>

        <ErrorDialog :show="showErrorDialog" @close="showErrorDialog = false">
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

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ImageInput from "@/Components/ImageInput.vue";
import Dropdown from "@/Components/Dropdown.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import { useDraggable } from "vue-draggable-plus";
import axios from "axios";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    product: {
        type: Object as () => ProductEntity,
        default: null,
    },
    variant: {
        type: Object as () => ProductVariantEntity,
        default: null,
    },
    isEdit: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["submit", "close", "submitted"]);

const isFile = (image) => {
    return image instanceof File;
};

const loadFile = (file) => {
    return URL.createObjectURL(file);
};

const isNewImage = (image) => {
    return typeof image.id == "string" && image.id.startsWith("new-var-");
};

const isExistingImage = (image) => {
    return typeof image.id == "number";
};

const initialNewImagesCount =
    props.variant?.images?.filter((image) => isNewImage(image)).length || 0;

const form = useForm(
    props.variant
        ? {
              ...JSON.parse(JSON.stringify(props.variant)),
              images: [
                  ...(props.variant?.images || []),
                  { id: `new-var-${initialNewImagesCount + 1}`, image: null },
              ],
          }
        : {
              product_id: props.product?.id,
              name: null,
              sku: null,
              barcode: null,
              motif: null,
              color_id: null,
              color: null,
              size_id: null,
              size: null,
              material: null,
              purchase_price: null,
              base_selling_price: null,
              discount_type: props.product?.discount_type || "percentage",
              discount: props.product?.discount,
              current_stock_level: null,
              unit: null,
              images: [{ id: "new-var-1", image: null }],
          }
);

const countNewImages = computed(() => {
    return form.images.filter((image) => isNewImage(image)).length;
});

const drag = ref(false);

const page = usePage();

const colors = (page.props.colors as ColorEntity[]) || [];
const colorSearch = ref("");
const isColorDropdownOpen = ref(false);
const filteredColors = computed(() => {
    return colors.filter((color) =>
        color.name.toLowerCase().includes(colorSearch.value.toLowerCase())
    );
});

const sizes = (page.props.sizes as SizeEntity[]) || [];
const sizeSearch = ref("");
const isSizeDropdownOpen = ref(false);
const filteredSizes = computed(() => {
    return sizes.filter((size) =>
        size.name.toLowerCase().includes(sizeSearch.value.toLowerCase())
    );
});

function validate() {
    if (form.errors) {
        console.log(form.errors);
        form.errors = {};
    }

    if (!form.motif) {
        form.errors.motif = "Motif tidak boleh kosong.";
    }

    if (!form.color_id) {
        form.errors.color_id = "Warna tidak boleh kosong.";
    }

    if (!form.size_id) {
        form.errors.size_id = "Ukuran tidak boleh kosong.";
    }

    if (!form.material) {
        form.errors.material = "Jenis bahan tidak boleh kosong.";
    }

    if (!form.base_selling_price) {
        form.errors.base_selling_price = "Harga dasar tidak boleh kosong.";
    }

    if (!form.current_stock_level) {
        form.errors.current_stock_level = "Stok tidak boleh kosong.";
    }

    if (!form.unit) {
        form.errors.unit = "Satuan tidak boleh kosong.";
    }

    if (!form.images || form.images.length === 0) {
        form.errors.images = "Minimal harus ada satu gambar produk.";
    }
}

function uploadNewImage(image, index) {
    const token = `Bearer ${localStorage.getItem("access_token")}`;

    const formData = new FormData();
    formData.append("product_variant_id", props.variant?.id?.toString());
    formData.append("product_id", props.product?.id?.toString());
    formData.append("image", image.image);
    formData.append("order", index);

    axios
        .post(
            `${page.props.ziggy.url}/api/admin/product-variant-image`,
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
            `${page.props.ziggy.url}/api/admin/product-variant-image/${image.id}`,
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
                `${page.props.ziggy.url}/api/admin/product-variant-image/${imageId}`,
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

function updateVariant() {
    const data = form.data();
    const formData = new FormData();

    formData.append("_method", "PUT");

    Object.keys(data).forEach((key) => {
        if (key === "images") return;

        if (data[key] !== null && data[key] !== undefined) {
            formData.append(key, data[key]);
        }
    });

    const token = `Bearer ${localStorage.getItem("access_token")}`;

    axios
        .post(
            `${page.props.ziggy.url}/api/admin/product-variant/${props.variant.id}`,
            formData,
            {
                headers: {
                    "Content-Type": "multipart/form-data",
                    Authorization: token,
                },
            }
        )
        .then((response) => {
            if (response.data?.meta?.message) {
                emit("submitted", response.data?.meta?.message);
            }
        })
        .catch((error) => {
            if (error.response?.data?.error) {
                openErrorDialog(error.response.data.error);
            }
        });
}

function createVariant() {
    const data = form.data();
    const formData = new FormData();

    Object.keys(data).forEach((key) => {
        if (key === "images") {
            data.images.forEach((image, index) => {
                if (isNewImage(image) && image.image instanceof File) {
                    formData.append(`images[${index}]`, image.image);
                }
            });
        } else if (data[key] !== null && data[key] !== undefined) {
            formData.append(key, data[key]);
        }
    });

    const token = `Bearer ${localStorage.getItem("access_token")}`;

    axios
        .post(`${page.props.ziggy.url}/api/admin/product-variant`, formData, {
            headers: {
                "Content-Type": "multipart/form-data",
                Authorization: token,
            },
        })
        .then((response) => {
            if (response.data?.meta?.message) {
                emit("submitted", response.data?.meta?.message);
            }
        })
        .catch((error) => {
            if (error.response?.data?.error) {
                openErrorDialog(error.response.data.error);
            }
        });
}

const submit = () => {
    validate();
    if (form.errors.url) return;

    if (props.isEdit) {
        if (props.variant) {
            updateImages();
            deleteImages();
            updateVariant();
        } else {
            createVariant();
        }
    } else {
        emit("submit", {
            ...form.data(),
            final_selling_price:
                form.base_selling_price -
                (form.discount_type === "percentage"
                    ? (form.base_selling_price * form.discount) / 100
                    : form.discount),
            images: form.images.filter(
                (image) => image.image instanceof File || isExistingImage(image)
            ),
        });
    }

    emit("close");
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

const imagesToDelete = ref([]);

const showErrorDialog = ref(false);
const errorMessage = ref(null);

const openErrorDialog = (message) => {
    errorMessage.value = message;
    showErrorDialog.value = true;
};
</script>

<template>
    <form @submit.prevent="submit" class="w-full p-2">
        <div class="flex flex-col items-start w-full gap-4 text-start">
            <h2
                class="w-full mb-3 text-lg font-medium text-center text-gray-900"
            >
                {{ props.variant ? "Ubah" : "Tambah" }} Variasi Produk
            </h2>

            <!-- Motif -->
            <div
                class="flex flex-col w-full gap-y-1 gap-x-4 sm:items-center sm:flex-row"
            >
                <InputLabel
                    for="motif"
                    value="Motif"
                    class="text-lg font-bold sm:w-1/5"
                />
                <span class="hidden text-sm sm:block">:</span>
                <TextInput
                    id="motif"
                    v-model="form.motif"
                    type="text"
                    placeholder="Masukkan Nama Motif"
                    class="block w-full mt-1"
                    required
                    autocomplete="motif"
                    :error="form.errors.motif"
                    @update:modelValue="form.errors.motif = null"
                />
            </div>

            <!-- Color -->
            <div
                class="flex flex-col w-full gap-y-1 gap-x-4 sm:items-center sm:flex-row"
            >
                <InputLabel
                    for="color_id"
                    value="Warna"
                    class="text-lg font-bold sm:w-1/5"
                />
                <span class="hidden text-sm sm:block">:</span>
                <Dropdown
                    id="color_id"
                    v-model="form.color_id"
                    :options="form.colors"
                    option-label="name"
                    option-value="id"
                    placeholder="Pilih Warna"
                    align="left"
                    class="block w-full mt-1"
                    required
                    :error="form.errors.color_id"
                    @update:modelValue="form.errors.color_id = null"
                    @onOpen="isColorDropdownOpen = true"
                    @onClose="isColorDropdownOpen = false"
                >
                    <template #trigger>
                        <TextInput
                            :modelValue="
                                form.color_id && !isColorDropdownOpen
                                    ? form.color.name
                                    : colorSearch
                            "
                            @update:modelValue="
                                form.color_id && !isColorDropdownOpen
                                    ? null
                                    : (colorSearch = $event)
                            "
                            class="w-full"
                            placeholder="Pilih Warna"
                        >
                            <template #suffix>
                                <button
                                    v-if="form.color_id && !isColorDropdownOpen"
                                    type="button"
                                    class="absolute p-[7px] text-gray-400 bg-white rounded-full top-1 right-1 hover:bg-gray-100 transition-all duration-300 ease-in-out"
                                    @click="
                                        form.color_id = null;
                                        form.color = null;
                                        colorSearch = '';
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
                                v-for="color in filteredColors"
                                :key="color.id"
                                @click="
                                    form.color_id = color.id;
                                    form.color = color;
                                "
                                class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-start"
                            >
                                {{ color.name }}
                            </li>
                        </ul>
                    </template>
                </Dropdown>
            </div>

            <!-- Size -->
            <div
                class="flex flex-col w-full gap-y-1 gap-x-4 sm:items-center sm:flex-row"
            >
                <InputLabel
                    for="size_id"
                    value="Ukuran"
                    class="text-lg font-bold sm:w-1/5"
                />
                <span class="hidden text-sm sm:block">:</span>
                <Dropdown
                    id="size_id"
                    v-model="form.size_id"
                    :options="form.sizes"
                    option-label="name"
                    option-value="id"
                    placeholder="Pilih Ukuran"
                    align="left"
                    class="block w-full mt-1"
                    required
                    :error="form.errors.size_id"
                    @update:modelValue="form.errors.size_id = null"
                    @onOpen="isSizeDropdownOpen = true"
                    @onClose="isSizeDropdownOpen = false"
                >
                    <template #trigger>
                        <TextInput
                            :modelValue="
                                form.size_id && !isSizeDropdownOpen
                                    ? form.size.name
                                    : sizeSearch
                            "
                            @update:modelValue="
                                form.size_id && !isSizeDropdownOpen
                                    ? null
                                    : (sizeSearch = $event)
                            "
                            class="w-full"
                            placeholder="Pilih Ukuran"
                        >
                            <template #suffix>
                                <button
                                    v-if="form.size_id && !isSizeDropdownOpen"
                                    type="button"
                                    class="absolute p-[7px] text-gray-400 bg-white rounded-full top-1 right-1 hover:bg-gray-100 transition-all duration-300 ease-in-out"
                                    @click="
                                        form.size_id = null;
                                        form.size = null;
                                        sizeSearch = '';
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
                                v-for="size in filteredSizes"
                                :key="size.id"
                                @click="
                                    form.size_id = size.id;
                                    form.size = size;
                                "
                                class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-start"
                            >
                                {{ size.name }}
                            </li>
                        </ul>
                    </template>
                </Dropdown>
            </div>

            <!-- Material -->
            <div
                class="flex flex-col w-full gap-y-1 gap-x-4 sm:items-center sm:flex-row"
            >
                <InputLabel
                    for="material"
                    value="Jenis Bahan"
                    class="text-lg font-bold sm:w-1/5"
                />
                <span class="hidden text-sm sm:block">:</span>
                <TextInput
                    id="material"
                    v-model="form.material"
                    type="text"
                    placeholder="Masukkan Nama Jenis Bahan"
                    class="block w-full mt-1"
                    required
                    autocomplete="material"
                    :error="form.errors.material"
                    @update:modelValue="form.errors.material = null"
                />
            </div>

            <!-- Images -->
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
                        :key="index"
                        :id="`image-${image.id}`"
                        :modelValue="image.image"
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
            </div>

            <!-- Base Selling Price -->
            <div
                class="flex flex-col w-full gap-y-1 gap-x-4 sm:items-center sm:flex-row"
            >
                <InputLabel
                    for="base_selling_price"
                    value="Harga "
                    class="text-lg font-bold sm:w-1/5"
                />
                <span class="hidden text-sm sm:block">:</span>
                <TextInput
                    id="base_selling_price"
                    v-model.number="form.base_selling_price"
                    type="number"
                    placeholder="Masukkan Harga"
                    class="block w-full mt-1"
                    required
                    autocomplete="base_selling_price"
                    :error="form.errors.base_selling_price"
                    @update:modelValue="form.errors.base_selling_price = null"
                />
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

            <!-- Stock -->
            <div
                class="flex flex-col w-full gap-y-1 gap-x-4 sm:items-center sm:flex-row"
            >
                <InputLabel
                    for="current_stock_level"
                    value="Stok"
                    class="text-lg font-bold sm:w-1/5"
                />
                <span class="hidden text-sm sm:block">:</span>
                <TextInput
                    id="current_stock_level"
                    v-model.number="form.current_stock_level"
                    type="number"
                    placeholder="Masukkan Stok"
                    class="block w-full mt-1"
                    required
                    autocomplete="current_stock_level"
                    :error="form.errors.current_stock_level"
                    @update:modelValue="form.errors.current_stock_level = null"
                />
            </div>

            <!-- Unit -->
            <div
                class="flex flex-col w-full gap-y-1 gap-x-4 sm:items-center sm:flex-row"
            >
                <InputLabel
                    for="unit"
                    value="Satuan"
                    class="text-lg font-bold sm:w-1/5"
                />
                <span class="hidden text-sm sm:block">:</span>
                <TextInput
                    id="unit"
                    v-model="form.unit"
                    type="text"
                    placeholder="Masukkan Nama Satuan"
                    class="block w-full mt-1"
                    required
                    autocomplete="unit"
                    :error="form.errors.unit"
                    @update:modelValue="form.errors.unit = null"
                />
            </div>

            <div class="flex items-center justify-start w-full gap-4 mt-4">
                <PrimaryButton type="submit"> Simpan Data </PrimaryButton>
                <SecondaryButton type="button" @click="$emit('close')">
                    Tutup
                </SecondaryButton>
            </div>
        </div>

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

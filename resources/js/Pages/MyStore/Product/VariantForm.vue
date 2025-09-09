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
import InputGroup from "@/Components/InputGroup.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import DialogModal from "@/Components/DialogModal.vue";
import ColorForm from "../Color/ColorForm.vue";

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
              unit_id: null,
              unit: null,
              images: [{ id: "new-var-1", image: null }],
          }
);

const countNewImages = computed(() => {
    return form.images.filter((image) => isNewImage(image)).length;
});

const drag = ref(false);

const page = usePage();

const colors = ref((page.props.colors as ColorEntity[]) || []);
const colorSearch = ref("");
const filteredColors = computed(() => {
    return colors.value.filter((color) =>
        color.name.toLowerCase().includes(colorSearch.value.toLowerCase())
    );
});

const sizes = ref((page.props.sizes as SizeEntity[]) || []);
const sizeSearch = ref("");
const filteredSizes = computed(() => {
    return sizes.value.filter((size) =>
        size.name.toLowerCase().includes(sizeSearch.value.toLowerCase())
    );
});

const units = ref((page.props.units as UnitEntity[]) || []);
const unitSearch = ref("");
const filteredUnits = computed(() => {
    return units.value.filter((unit) =>
        unit.name.toLowerCase().includes(unitSearch.value.toLowerCase())
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
        .post("/api/my-store/product-variant-image", formData, {
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
        .post(`/api/my-store/product-variant-image/${image.id}`, formData, {
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
            .delete(`/api/my-store/product-variant-image/${imageId}`, {
                headers: {
                    Authorization: token,
                },
            })
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
        .post(`/api/my-store/product-variant/${props.variant.id}`, formData, {
            headers: {
                "Content-Type": "multipart/form-data",
                Authorization: token,
            },
        })
        .then((response) => {
            if (response.data?.meta?.message) {
                emit("submitted", response.data?.meta?.message);
                emit("close");
            }
        })
        .catch((error) => {
            if (error.response?.data?.errors) {
                Object.keys(error.response.data.errors).forEach((key) => {
                    form.errors[key] = error.response.data.errors[key][0];
                });
            } else if (error.response?.data?.error) {
                openErrorDialog(error.response.data.error);
            }
        });
}

function createVariant() {
    const data = form.data();
    const formData = new FormData();

    formData.append("store_id", localStorage.getItem("selected_store_id"));

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
        .post("/api/my-store/product-variant", formData, {
            headers: {
                "Content-Type": "multipart/form-data",
                Authorization: token,
            },
        })
        .then((response) => {
            if (response.data?.meta?.message) {
                emit("submitted", response.data?.meta?.message);
                emit("close");
            }
        })
        .catch((error) => {
            if (error.response?.data?.errors) {
                Object.keys(error.response.data.errors).forEach((key) => {
                    form.errors[key] = error.response.data.errors[key][0];
                });
            } else if (error.response?.data?.error) {
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

        emit("close");
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

const imagesToDelete = ref([]);

const showSuccessDialog = ref(false);
const successMessage = ref(null);

const showAddColorForm = ref(false);
const showAddSizeForm = ref(false);
const showAddUnitForm = ref(false);

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
            <InputGroup id="motif" label="Motif">
                <TextInput
                    id="motif"
                    v-model="form.motif"
                    type="text"
                    placeholder="Masukkan Nama Motif"
                    required
                    autocomplete="motif"
                    :error="form.errors.motif"
                    @update:modelValue="form.errors.motif = null"
                />
            </InputGroup>

            <!-- Material -->
            <InputGroup id="material" label="Jenis Bahan">
                <TextInput
                    id="material"
                    v-model="form.material"
                    type="text"
                    placeholder="Masukkan Nama Jenis Bahan"
                    required
                    autocomplete="material"
                    :error="form.errors.material"
                    @update:modelValue="form.errors.material = null"
                />
            </InputGroup>

            <div class="flex items-center w-full gap-4">
                <!-- Color -->
                <InputGroup id="color_id" label="Warna">
                    <DropdownSearchInput
                        id="color_id"
                        :modelValue="
                            form.color_id
                                ? {
                                      label: form.color?.name,
                                      value: form.color_id,
                                      hexCode: form.color?.hex_code,
                                  }
                                : null
                        "
                        :options="
                            filteredColors.map((color) => ({
                                label: color.name,
                                value: color.id,
                                hexCode: color.hex_code,
                            }))
                        "
                        placeholder="Pilih Warna"
                        :error="form.errors.color_id"
                        @update:modelValue="
                            (option) => {
                                form.color_id = option?.value;
                                form.color = option
                                    ? filteredColors.find(
                                          (color) => color.id === option.value
                                      )
                                    : null;
                            }
                        "
                        @search="colorSearch = $event"
                        @clear="
                            form.color_id = null;
                            form.color = null;
                            colorSearch = '';
                        "
                    >
                        <template #optionHeader>
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <p class="text-sm font-semibold">Pilih Warna</p>
                                <button
                                    type="button"
                                    class="text-sm text-blue-500 hover:underline"
                                    @click="showAddColorForm = true"
                                >
                                    Tambah
                                </button>
                            </div>
                        </template>
                    </DropdownSearchInput>
                </InputGroup>

                <!-- Size -->
                <InputGroup id="size_id" label="Ukuran">
                    <DropdownSearchInput
                        id="size_id"
                        :modelValue="
                            form.size_id
                                ? {
                                      label: form.size?.name,
                                      value: form.size_id,
                                  }
                                : null
                        "
                        :options="
                            filteredSizes.map((size) => ({
                                label: size.name,
                                value: size.id,
                            }))
                        "
                        placeholder="Pilih Ukuran"
                        :error="form.errors.size_id"
                        @update:modelValue="
                            (option) => {
                                form.size_id = option?.value;
                                form.size = option
                                    ? filteredSizes.find(
                                          (size) => size.id === option.value
                                      )
                                    : null;
                            }
                        "
                        @search="sizeSearch = $event"
                        @clear="
                            form.size_id = null;
                            form.size = null;
                            sizeSearch = '';
                        "
                    >
                        <template #optionHeader>
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <p class="text-sm font-semibold">
                                    Pilih Ukuran
                                </p>
                                <button
                                    type="button"
                                    class="text-sm text-blue-500 hover:underline"
                                    @click="showAddSizeForm = true"
                                >
                                    Tambah
                                </button>
                            </div>
                        </template>
                    </DropdownSearchInput>
                </InputGroup>
            </div>

            <div class="flex items-center w-full gap-4">
                <!-- Stock -->
                <InputGroup id="current_stock_level" label="Stok">
                    <TextInput
                        id="current_stock_level"
                        v-model.number="form.current_stock_level"
                        type="number"
                        placeholder="Masukkan Stok"
                        required
                        autocomplete="current_stock_level"
                        :error="form.errors.current_stock_level"
                        @update:modelValue="
                            form.errors.current_stock_level = null
                        "
                    />
                </InputGroup>

                <!-- Unit -->
                <InputGroup id="unit" label="Satuan">
                    <DropdownSearchInput
                        id="unit_id"
                        :modelValue="
                            form.unit_id
                                ? {
                                      label: form.unit?.name,
                                      value: form.unit_id,
                                  }
                                : null
                        "
                        :options="
                            filteredUnits.map((unit) => ({
                                label: unit.name,
                                value: unit.id,
                            }))
                        "
                        placeholder="Pilih Satuan"
                        :error="form.errors.unit_id"
                        @update:modelValue="
                            (option) => {
                                form.unit_id = option?.value;
                                form.unit = option
                                    ? filteredUnits.find(
                                          (unit) => unit.id === option.value
                                      )
                                    : null;
                            }
                        "
                        @search="unitSearch = $event"
                        @clear="
                            form.unit_id = null;
                            form.unit = null;
                            unitSearch = '';
                        "
                    >
                        <template #optionHeader>
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <p class="text-sm font-semibold">
                                    Pilih Satuan
                                </p>
                                <button
                                    type="button"
                                    class="text-sm text-blue-500 hover:underline"
                                    @click="showAddColorForm = true"
                                >
                                    Tambah
                                </button>
                            </div>
                        </template>
                    </DropdownSearchInput>
                </InputGroup>
            </div>

            <div class="flex items-center w-full gap-4">
                <!-- Base Selling Price -->
                <InputGroup id="base_selling_price" label="Harga Dasar">
                    <TextInput
                        id="base_selling_price"
                        v-model.number="form.base_selling_price"
                        type="number"
                        placeholder="Masukkan Harga"
                        required
                        autocomplete="base_selling_price"
                        :error="form.errors.base_selling_price"
                        @update:modelValue="
                            form.errors.base_selling_price = null
                        "
                    />
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
            </div>

            <!-- Images -->
            <InputGroup id="images" label="Gambar Variasi Produk">
                <div ref="imagesContainer" class="flex flex-wrap w-full gap-2">
                    <ImageInput
                        v-for="(image, index) in form.images"
                        :key="index"
                        :id="`image-${image.id}`"
                        :modelValue="image.image"
                        type="file"
                        accept="image/*"
                        placeholder="Upload gambar"
                        class="!w-auto mt-1"
                        width="!w-[100px]"
                        height="h-[100px]"
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

            <div class="flex items-center justify-start w-full gap-4 mt-4">
                <PrimaryButton type="submit"> Simpan Data </PrimaryButton>
                <SecondaryButton type="button" @click="$emit('close')">
                    Batalkan
                </SecondaryButton>
            </div>
        </div>

        <!-- Add Color Modal -->
        <DialogModal
            :show="showAddColorForm"
            @close="showAddColorForm = false"
            maxWidth="sm"
        >
            <template #content>
                <div class="w-full">
                    <h2
                        class="w-full mb-3 text-lg font-medium text-center text-gray-900"
                    >
                        Tambah Warna
                    </h2>
                    <ColorForm
                        :isDialog="true"
                        @onSubmitted="
                            (brandName) => {
                                showAddColorForm = false;
                                colorSearch = '';

                                colors = $page.props.colors as ColorEntity[];
                                
                                const newColor = colors.find(
                                    (color) => color.name === brandName
                                );
                                form.color_id = newColor.id;
                                form.color = newColor;

                                openSuccessDialog(
                                    'Warna berhasil ditambahkan.'
                                );
                            }
                        "
                        @close="showAddColorForm = false"
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

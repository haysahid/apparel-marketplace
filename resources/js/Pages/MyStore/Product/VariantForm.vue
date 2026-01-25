<script setup lang="ts">
import { ref, computed, onMounted, nextTick, watch } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { useDraggable } from "vue-draggable-plus";
import axios from "axios";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputGroup from "@/Components/InputGroup.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import DialogModal from "@/Components/DialogModal.vue";
import ColorForm from "../Color/ColorForm.vue";
import UnitForm from "../Unit/UnitForm.vue";
import SizeForm from "../Size/SizeForm.vue";
import cookieManager from "@/plugins/cookie-manager";
import MediaCard from "@/Components/MediaCard.vue";
import Modal from "@/Components/Modal.vue";
import MediaForm from "@/Components/MediaForm.vue";
import { useDialogStore } from "@/stores/dialog-store";
import { useImageViewerStore } from "@/stores/image-viewer-store";

const props = defineProps({
    product: {
        type: Object as () => ProductEntity,
        default: null,
    },
    variant: {
        type: Object as () => ProductVariantEntity,
        default: null,
    },
});

const emit = defineEmits(["close", "submitted"]);

const dialogStore = useDialogStore();

const form = useForm(
    props.variant
        ? {
              ...JSON.parse(JSON.stringify(props.variant)),
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
              images: [],
          },
);
const sellingMarginPercentage = ref(0);
const sellingMarginPercentageError = ref<string | null>(null);

const computedSku = computed(() => {
    const skuSeparator = "-";
    const skuPrefix = props.product.sku_prefix;
    let metadataList = [];

    if (form.motif) {
        metadataList.push(form.motif);
    }
    if (form.color) {
        metadataList.push(form.color.name);
    }
    if (form.size) {
        metadataList.push(form.size.name);
    }

    return (
        skuPrefix +
        (metadataList.length > 0
            ? skuSeparator +
              metadataList
                  .map((item) =>
                      item.toString().toUpperCase().replace(/\s+/g, ""),
                  )
                  .join(skuSeparator)
            : "")
    );
});

const computedFinalSellingPrice = computed(() => {
    let finalPrice = form.base_selling_price || 0;

    if (form.discount && form.discount > 0) {
        if (form.discount_type === "percentage") {
            finalPrice = finalPrice - (finalPrice * form.discount) / 100;
        } else if (form.discount_type === "fixed") {
            finalPrice = finalPrice - form.discount;
        }
    }

    return finalPrice >= 0 ? finalPrice : 0;
});

const drag = ref(false);

const page = usePage();

const colors = ref((page.props.colors as ColorEntity[]) || []);
const colorSearch = ref("");
const filteredColors = computed(() => {
    return colors.value.filter((color) =>
        color.name.toLowerCase().includes(colorSearch.value.toLowerCase()),
    );
});

const sizes = ref((page.props.sizes as SizeEntity[]) || []);
const sizeSearch = ref("");
const filteredSizes = computed(() => {
    return sizes.value.filter((size) =>
        size.name.toLowerCase().includes(sizeSearch.value.toLowerCase()),
    );
});

const units = ref((page.props.units as UnitEntity[]) || []);
const unitSearch = ref("");
const filteredUnits = computed(() => {
    return units.value.filter((unit) =>
        unit.name.toLowerCase().includes(unitSearch.value.toLowerCase()),
    );
});

watch(
    () => form.discount,
    () => {
        if (
            form.discount_type === "percentage" &&
            (form.discount > 100 || form.discount < 0)
        ) {
            form.errors.discount = "Diskon harus antara 0 hingga 100.";
        } else {
            form.errors.discount = null;
        }
    },
);

watch(
    () => sellingMarginPercentage.value,
    (newValue) => {
        if (newValue < 0 || newValue > 100) {
            sellingMarginPercentageError.value =
                "Persentase margin harus antara 0 hingga 100.";
            return;
        } else {
            sellingMarginPercentageError.value = null;
        }
    },
);

function validate() {
    if (form.errors) {
        console.log(form.errors);
        form.errors = {};
    }

    if (!form.color_id) {
        form.errors.color_id = "Warna tidak boleh kosong.";
    }

    if (!form.size_id) {
        form.errors.size_id = "Ukuran tidak boleh kosong.";
    }

    if (!form.base_selling_price) {
        form.errors.base_selling_price = "Harga dasar tidak boleh kosong.";
    }

    if (!form.current_stock_level) {
        form.errors.current_stock_level = "Stok tidak boleh kosong.";
    }

    if (!form.unit_id) {
        form.errors.unit_id = "Satuan tidak boleh kosong.";
    }
}

function updateVariant() {
    const data = form.data();
    const formData = new FormData();

    formData.append("_method", "PUT");

    Object.keys(data).forEach((key) => {
        if (key === "images") {
            data.images.forEach((image: ProductVariantImageEntity, index) => {
                formData.append(`images[${index}]`, image.media_id.toString());
            });
        } else if (data[key] !== null && data[key] !== undefined) {
            formData.append(key, data[key]);
        }
    });

    const token = `Bearer ${cookieManager.getItem("access_token")}`;

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
                dialogStore.openErrorDialog(error.response.data.error);
            }
        });
}

function createVariant() {
    const data = form.data();
    const formData = new FormData();

    formData.append("store_id", cookieManager.getItem("selected_store_id"));

    Object.keys(data).forEach((key) => {
        if (key === "images") {
            data.images.forEach((image: ProductVariantImageEntity, index) => {
                formData.append(`images[${index}]`, image.media_id.toString());
            });
        } else if (data[key] !== null && data[key] !== undefined) {
            formData.append(key, data[key]);
        }
    });

    const token = `Bearer ${cookieManager.getItem("access_token")}`;

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
                dialogStore.openErrorDialog(error.response.data.error);
            }
        });
}

const submit = () => {
    validate();
    if (Object.keys(form.errors).length > 0) return;

    if (props.variant) {
        updateVariant();
    } else {
        createVariant();
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

const showAddColorForm = ref(false);
const showAddSizeForm = ref(false);
const showAddUnitForm = ref(false);

onMounted(() => {
    nextTick(() => {
        const input = document.getElementById("barcode") as HTMLInputElement;
        input?.focus();
    });

    // If editing existing variant, calculate initial selling margin percentage
    if (props.variant) {
        if (form.purchase_price && form.purchase_price > 0) {
            sellingMarginPercentage.value =
                ((form.base_selling_price - form.purchase_price) /
                    form.purchase_price) *
                100;
        } else {
            sellingMarginPercentage.value = 0;
        }
    }
});

const showMediaFormModal = ref(false);
</script>

<template>
    <form @submit.prevent="submit" class="w-full">
        <div class="flex flex-col items-start w-full text-start">
            <h2
                class="w-full p-4 text-lg font-medium text-center text-gray-900 sm:p-6"
            >
                {{ props.variant ? "Ubah" : "Tambah" }} Variasi Produk
            </h2>

            <div
                class="flex flex-col items-start w-full gap-4 text-start overflow-y-auto h-[60vh] px-4 sm:px-6"
            >
                <div class="flex items-center w-full gap-4">
                    <!-- Barcode -->
                    <InputGroup for="barcode" label="Barcode">
                        <TextInput
                            id="barcode"
                            v-model="form.barcode"
                            type="text"
                            placeholder="Masukkan Barcode"
                            autocomplete="on"
                            :error="form.errors.barcode"
                            @update:modelValue="form.errors.barcode = null"
                        />
                    </InputGroup>

                    <!-- SKU -->
                    <InputGroup for="sku" label="SKU">
                        <TextInput
                            id="sku"
                            :modelValue="computedSku"
                            type="text"
                            readonly
                            bgClass="bg-gray-100"
                        />
                        <template #suffix>
                            <span class="text-sm italic text-gray-500">
                                - Otomatis
                            </span>
                        </template>
                    </InputGroup>
                </div>

                <!-- Motif -->
                <InputGroup for="motif" label="Motif">
                    <TextInput
                        id="motif"
                        v-model="form.motif"
                        type="text"
                        placeholder="Masukkan Nama Motif"
                        autocomplete="on"
                        :error="form.errors.motif"
                        @update:modelValue="form.errors.motif = null"
                    />
                </InputGroup>

                <!-- Material -->
                <InputGroup for="material" label="Jenis Bahan">
                    <TextInput
                        id="material"
                        v-model="form.material"
                        type="text"
                        placeholder="Masukkan Nama Jenis Bahan"
                        autocomplete="on"
                        :error="form.errors.material"
                        @update:modelValue="form.errors.material = null"
                    />
                </InputGroup>

                <div class="flex items-center w-full gap-4">
                    <!-- Color -->
                    <InputGroup for="color_id" label="Warna" required>
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
                            required
                            placeholder="Pilih Warna"
                            :error="form.errors.color_id"
                            @update:modelValue="
                                (option) => {
                                    form.color_id = option?.value;
                                    form.color = option
                                        ? filteredColors.find(
                                              (color) =>
                                                  color.id === option.value,
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
                                    <p class="text-sm font-semibold">
                                        Pilih Warna
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

                    <!-- Size -->
                    <InputGroup for="size_id" label="Ukuran" required>
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
                            required
                            placeholder="Pilih Ukuran"
                            :error="form.errors.size_id"
                            @update:modelValue="
                                (option) => {
                                    form.size_id = option?.value;
                                    form.size = option
                                        ? filteredSizes.find(
                                              (size) =>
                                                  size.id === option.value,
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
                    <InputGroup for="current_stock_level" label="Stok" required>
                        <TextInput
                            id="current_stock_level"
                            v-model.number="form.current_stock_level"
                            type="number"
                            placeholder="Masukkan Stok"
                            required
                            :error="form.errors.current_stock_level"
                            @update:modelValue="
                                form.errors.current_stock_level = null
                            "
                        />
                    </InputGroup>

                    <!-- Unit -->
                    <InputGroup for="unit_id" label="Satuan" required>
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
                            required
                            placeholder="Pilih Satuan"
                            :error="form.errors.unit_id"
                            @update:modelValue="
                                (option) => {
                                    form.unit_id = option?.value;
                                    form.unit = option
                                        ? filteredUnits.find(
                                              (unit) =>
                                                  unit.id === option.value,
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
                                        @click="showAddUnitForm = true"
                                    >
                                        Tambah
                                    </button>
                                </div>
                            </template>
                        </DropdownSearchInput>
                    </InputGroup>
                </div>

                <div class="flex flex-col w-full gap-4 sm:flex-row">
                    <!-- Purchase Price -->
                    <InputGroup for="purchase_price" label="Harga Beli">
                        <TextInput
                            id="purchase_price"
                            v-model.number="form.purchase_price"
                            type="number"
                            placeholder="Masukkan Harga Beli"
                            :error="form.errors.purchase_price"
                            @update:modelValue="
                                form.errors.purchase_price = null;

                                form.base_selling_price =
                                    (form.purchase_price || 0) +
                                    ((form.purchase_price || 0) *
                                        sellingMarginPercentage) /
                                        100;
                            "
                        />
                    </InputGroup>

                    <!-- Base Selling Margin -->
                    <InputGroup
                        for="base_selling_price"
                        label="Margin Penjualan Dasar (%)"
                    >
                        <TextInput
                            id="selling_margin_percentage"
                            v-model.number="sellingMarginPercentage"
                            type="number"
                            placeholder="Masukkan Margin Penjualan"
                            :error="sellingMarginPercentageError"
                            @update:modelValue="
                                sellingMarginPercentageError = null;

                                form.base_selling_price =
                                    (form.purchase_price || 0) +
                                    ((form.purchase_price || 0) *
                                        sellingMarginPercentage) /
                                        100;
                            "
                        />
                    </InputGroup>
                </div>

                <div class="flex flex-col w-full gap-4 sm:flex-row">
                    <!-- Base Selling Price -->
                    <InputGroup
                        for="base_selling_price"
                        label="Harga Jual Dasar"
                        required
                    >
                        <TextInput
                            id="base_selling_price"
                            v-model.number="form.base_selling_price"
                            type="number"
                            placeholder="Masukkan Harga Jual Dasar"
                            required
                            :error="form.errors.base_selling_price"
                            @update:modelValue="
                                form.errors.base_selling_price = null;

                                sellingMarginPercentage = form.purchase_price
                                    ? ((form.base_selling_price -
                                          form.purchase_price) /
                                          form.purchase_price) *
                                      100
                                    : 0;
                            "
                        />
                    </InputGroup>

                    <!-- Final Selling Price -->
                    <InputGroup
                        for="final_selling_price"
                        label="Harga Jual Akhir"
                    >
                        <TextInput
                            id="final_selling_price"
                            :modelValue="
                                $formatCurrency(computedFinalSellingPrice)
                            "
                            type="text"
                            readonly
                            bgClass="bg-gray-100"
                        />
                        <template #suffix>
                            <span class="text-sm italic text-gray-500">
                                - Otomatis
                            </span>
                        </template>
                    </InputGroup>
                </div>

                <div class="flex flex-col w-full gap-4 sm:flex-row">
                    <!-- Discount -->
                    <InputGroup for="discount" label="Diskon (%)" required>
                        <TextInput
                            id="discount"
                            v-model.number="form.discount"
                            type="number"
                            placeholder="Masukkan Diskon"
                            required
                            :error="form.errors.discount"
                            @update:modelValue="form.errors.discount = null"
                        />
                    </InputGroup>

                    <!-- Final Selling Margin -->
                    <InputGroup
                        for="final_selling_margin"
                        label="Margin Penjualan Akhir"
                    >
                        <TextInput
                            id="final_selling_margin"
                            :modelValue="`${$formatCurrency(
                                computedFinalSellingPrice -
                                    (form.purchase_price || 0),
                            )} (${
                                form.purchase_price
                                    ? ((computedFinalSellingPrice -
                                          form.purchase_price) /
                                          form.purchase_price) *
                                      100
                                    : 0
                            }%)`"
                            type="text"
                            readonly
                            bgClass="bg-gray-100"
                        />

                        <template #suffix>
                            <span class="text-sm italic text-gray-500">
                                - Otomatis
                            </span>
                        </template>
                    </InputGroup>
                </div>

                <!-- Images -->
                <InputGroup for="images" label="Gambar Variasi Produk">
                    <div
                        ref="imagesContainer"
                        class="flex flex-wrap w-full gap-2"
                    >
                        <div
                            ref="imagesContainer"
                            class="flex flex-wrap w-full gap-2"
                        >
                            <template
                                v-for="(image, index) in form.images"
                                :key="image.id"
                            >
                                <MediaCard
                                    :media="image.media"
                                    :isSelected="false"
                                    :showCheckbox="false"
                                    :showRemoveButton="true"
                                    :showName="false"
                                    :showSize="false"
                                    @remove="form.images.splice(index, 1)"
                                    @click="
                                        useImageViewerStore().selectedImage =
                                            image.media
                                    "
                                />
                            </template>

                            <button
                                type="button"
                                class="relative overflow-hidden transition-all ease-in-out border rounded-lg cursor-pointer group hover:border-primary-light hover:ring-1 hover:ring-primary-light"
                                @click.prevent="showMediaFormModal = true"
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
                    </div>
                </InputGroup>
            </div>

            <div
                class="flex items-center justify-start w-full gap-4 p-4 sm:p-6"
            >
                <PrimaryButton type="submit"> Simpan Data </PrimaryButton>
                <SecondaryButton type="button" @click="$emit('close')">
                    Batalkan
                </SecondaryButton>
            </div>
        </div>
    </form>

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
                        (colorName) => {
                            showAddColorForm = false;
                            colorSearch = '';

                            colors = $page.props.colors as ColorEntity[];

                            const newColor = colors.find(
                                (color) => color.name === colorName,
                            );
                            form.color_id = newColor.id;
                            form.color = newColor;

                            dialogStore.openSuccessDialog(
                                'Warna berhasil ditambahkan.',
                            );
                        }
                    "
                    @close="showAddColorForm = false"
                    class="w-full"
                />
            </div>
        </template>
    </DialogModal>

    <!-- Add Size Modal -->
    <DialogModal
        :show="showAddSizeForm"
        @close="showAddSizeForm = false"
        maxWidth="sm"
    >
        <template #content>
            <div class="w-full">
                <h2
                    class="w-full mb-3 text-lg font-medium text-center text-gray-900"
                >
                    Tambah Ukuran
                </h2>
                <SizeForm
                    :isDialog="true"
                    @onSubmitted="
                        (sizeName) => {
                            showAddSizeForm = false;
                            sizeSearch = '';

                            sizes = $page.props.sizes as SizeEntity[];

                            const newSize = sizes.find(
                                (size) => size.name === sizeName,
                            );
                            form.size_id = newSize.id;
                            form.size = newSize;

                            dialogStore.openSuccessDialog(
                                'Ukuran berhasil ditambahkan.',
                            );
                        }
                    "
                    @close="showAddSizeForm = false"
                    class="w-full"
                />
            </div>
        </template>
    </DialogModal>

    <!-- Add Unit Modal -->
    <DialogModal
        :show="showAddUnitForm"
        @close="showAddUnitForm = false"
        maxWidth="sm"
    >
        <template #content>
            <div class="w-full">
                <h2
                    class="w-full mb-3 text-lg font-medium text-center text-gray-900"
                >
                    Tambah Satuan
                </h2>
                <UnitForm
                    :isDialog="true"
                    @onSubmitted="
                        (unitName) => {
                            showAddUnitForm = false;
                            unitSearch = '';

                            units = $page.props.units as UnitEntity[];

                            const newUnit = units.find(
                                (unit) => unit.name === unitName,
                            );
                            form.unit_id = newUnit.id;
                            form.unit = newUnit;

                            dialogStore.openSuccessDialog(
                                'Satuan berhasil ditambahkan.',
                            );
                        }
                    "
                    @close="showAddUnitForm = false"
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
            :relatedModelOnly="true"
            @close="showMediaFormModal = false"
            @selectedMediaList="
                (selectedMediaList) => {
                    form.images = [
                        ...form.images,
                        ...(selectedMediaList.map((media: MediaEntity) => ({
                            media_id: media.id,
                            original_url: media.original_url,
                            media: media,
                        })) as ProductVariantImageEntity[]),
                    ];
                    showMediaFormModal = false;
                }
            "
        />
    </Modal>
</template>

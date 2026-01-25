<script setup lang="ts">
import { ref, computed } from "vue";
import VariantForm from "./VariantForm.vue";
import VariantCard from "./VariantCard.vue";
import DialogModal from "@/Components/DialogModal.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Dropdown from "@/Components/Dropdown.vue";
import Chip from "@/Components/Chip.vue";
import ColorChip from "@/Components/ColorChip.vue";
import VariantFilter from "@/Components/VariantFilter.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    product: {
        type: Object as () => ProductEntity,
        default: null,
    },
    variants: {
        type: Array as () => ProductVariantEntity[],
        required: true,
    },
    isEdit: {
        type: Boolean,
        default: false,
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["onCreated", "onUpdated", "onDelete"]);

const motifs = computed(() => {
    return [
        ...new Set(
            props.variants
                .map((variant) => variant.motif)
                .filter((motif) => motif != null),
        ),
    ];
});

const colors = computed(() => {
    return [
        ...new Set(
            props.variants.map((variant) => JSON.stringify(variant.color)),
        ),
    ].map((color) => JSON.parse(color));
});

const selectedMotif = ref(null);
const selectedColor = ref(null);

const filteredVariants = computed(() => {
    const variants = props.variants.filter((variant) => {
        const matchesMotif =
            !selectedMotif.value || variant.motif === selectedMotif.value;
        const matchesColor =
            !selectedColor.value || variant.color_id === selectedColor.value.id;
        return matchesMotif && matchesColor;
    });

    return variants;
});

const showAddVariantForm = ref(false);
const openAddVariantForm = () => {
    showAddVariantForm.value = true;
};

const getVariantName = (variant: ProductVariantEntity) => {
    const metadata = [];

    if (variant.motif) {
        metadata.push(variant.motif);
    }
    if (variant.color) {
        metadata.push(variant.color.name);
    }
    if (variant.size) {
        metadata.push(variant.size.name);
    }

    return metadata.join(" - ");
};
</script>

<template>
    <div class="relative flex flex-col items-start w-full gap-2">
        <!-- Header -->
        <div class="sticky flex flex-col items-start w-full gap-2">
            <div class="flex items-start justify-between w-full gap-2">
                <div>
                    <h2 class="font-semibold text-left">
                        Variasi Produk ({{ props.variants.length }})
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{
                            (selectedMotif || selectedColor) &&
                            filteredVariants.length == 0
                                ? "Tidak ada variasi yang ditemukan."
                                : "Diperlukan minimal 1 variasi produk."
                        }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <PrimaryButton
                        v-if="props.variants.length"
                        type="button"
                        class="text-nowrap"
                        :disabled="props.isLoading"
                        @click="openAddVariantForm"
                    >
                        Tambah Variasi
                    </PrimaryButton>
                    <Dropdown
                        v-if="props.variants.length > 0"
                        align="right"
                        width="sm"
                        :autoClose="false"
                    >
                        <template #trigger>
                            <SecondaryButton
                                type="button"
                                class="!p-2 group"
                                :class="{
                                    'border-primary hover:bg-primary/10':
                                        selectedMotif || selectedColor,
                                }"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    class="transition-all duration-200 ease-in-out fill-gray-500 size-5 group-hover:fill-gray-600"
                                    :class="{
                                        'fill-primary group-hover:!fill-primary':
                                            selectedMotif || selectedColor,
                                    }"
                                >
                                    <path
                                        d="M9 5.00001C8.73478 5.00001 8.48043 5.10537 8.29289 5.2929C8.10536 5.48044 8 5.73479 8 6.00001C8 6.26523 8.10536 6.51958 8.29289 6.70712C8.48043 6.89465 8.73478 7.00001 9 7.00001C9.26522 7.00001 9.51957 6.89465 9.70711 6.70712C9.89464 6.51958 10 6.26523 10 6.00001C10 5.73479 9.89464 5.48044 9.70711 5.2929C9.51957 5.10537 9.26522 5.00001 9 5.00001ZM6.17 5.00001C6.3766 4.41448 6.75974 3.90744 7.2666 3.5488C7.77346 3.19015 8.37909 2.99756 9 2.99756C9.62091 2.99756 10.2265 3.19015 10.7334 3.5488C11.2403 3.90744 11.6234 4.41448 11.83 5.00001H19C19.2652 5.00001 19.5196 5.10537 19.7071 5.2929C19.8946 5.48044 20 5.73479 20 6.00001C20 6.26523 19.8946 6.51958 19.7071 6.70712C19.5196 6.89465 19.2652 7.00001 19 7.00001H11.83C11.6234 7.58554 11.2403 8.09258 10.7334 8.45122C10.2265 8.80986 9.62091 9.00246 9 9.00246C8.37909 9.00246 7.77346 8.80986 7.2666 8.45122C6.75974 8.09258 6.3766 7.58554 6.17 7.00001H5C4.73478 7.00001 4.48043 6.89465 4.29289 6.70712C4.10536 6.51958 4 6.26523 4 6.00001C4 5.73479 4.10536 5.48044 4.29289 5.2929C4.48043 5.10537 4.73478 5.00001 5 5.00001H6.17ZM15 11C14.7348 11 14.4804 11.1054 14.2929 11.2929C14.1054 11.4804 14 11.7348 14 12C14 12.2652 14.1054 12.5196 14.2929 12.7071C14.4804 12.8947 14.7348 13 15 13C15.2652 13 15.5196 12.8947 15.7071 12.7071C15.8946 12.5196 16 12.2652 16 12C16 11.7348 15.8946 11.4804 15.7071 11.2929C15.5196 11.1054 15.2652 11 15 11ZM12.17 11C12.3766 10.4145 12.7597 9.90744 13.2666 9.5488C13.7735 9.19015 14.3791 8.99756 15 8.99756C15.6209 8.99756 16.2265 9.19015 16.7334 9.5488C17.2403 9.90744 17.6234 10.4145 17.83 11H19C19.2652 11 19.5196 11.1054 19.7071 11.2929C19.8946 11.4804 20 11.7348 20 12C20 12.2652 19.8946 12.5196 19.7071 12.7071C19.5196 12.8947 19.2652 13 19 13H17.83C17.6234 13.5855 17.2403 14.0926 16.7334 14.4512C16.2265 14.8099 15.6209 15.0025 15 15.0025C14.3791 15.0025 13.7735 14.8099 13.2666 14.4512C12.7597 14.0926 12.3766 13.5855 12.17 13H5C4.73478 13 4.48043 12.8947 4.29289 12.7071C4.10536 12.5196 4 12.2652 4 12C4 11.7348 4.10536 11.4804 4.29289 11.2929C4.48043 11.1054 4.73478 11 5 11H12.17ZM9 17C8.73478 17 8.48043 17.1054 8.29289 17.2929C8.10536 17.4804 8 17.7348 8 18C8 18.2652 8.10536 18.5196 8.29289 18.7071C8.48043 18.8947 8.73478 19 9 19C9.26522 19 9.51957 18.8947 9.70711 18.7071C9.89464 18.5196 10 18.2652 10 18C10 17.7348 9.89464 17.4804 9.70711 17.2929C9.51957 17.1054 9.26522 17 9 17ZM6.17 17C6.3766 16.4145 6.75974 15.9074 7.2666 15.5488C7.77346 15.1902 8.37909 14.9976 9 14.9976C9.62091 14.9976 10.2265 15.1902 10.7334 15.5488C11.2403 15.9074 11.6234 16.4145 11.83 17H19C19.2652 17 19.5196 17.1054 19.7071 17.2929C19.8946 17.4804 20 17.7348 20 18C20 18.2652 19.8946 18.5196 19.7071 18.7071C19.5196 18.8947 19.2652 19 19 19H11.83C11.6234 19.5855 11.2403 20.0926 10.7334 20.4512C10.2265 20.8099 9.62091 21.0025 9 21.0025C8.37909 21.0025 7.77346 20.8099 7.2666 20.4512C6.75974 20.0926 6.3766 19.5855 6.17 19H5C4.73478 19 4.48043 18.8947 4.29289 18.7071C4.10536 18.5196 4 18.2652 4 18C4 17.7348 4.10536 17.4804 4.29289 17.2929C4.48043 17.1054 4.73478 17 5 17H6.17Z"
                                    />
                                </svg>
                            </SecondaryButton>
                        </template>
                        <template #content>
                            <VariantFilter
                                :motifs="motifs"
                                :colors="colors"
                                v-model:selectedMotif="selectedMotif"
                                v-model:selectedColor="selectedColor"
                                class="p-4"
                            />
                        </template>
                    </Dropdown>
                </div>
            </div>

            <!-- Active Filter -->
            <div
                v-if="selectedMotif || selectedColor"
                class="flex flex-wrap items-center justify-between w-full gap-2 mt-1"
            >
                <div class="flex items-center gap-2">
                    <Chip
                        v-if="selectedMotif"
                        :label="selectedMotif"
                        :selected="true"
                        class="pr-2 !gap-1 !cursor-default text-sm"
                    >
                        <template #suffix>
                            <button
                                type="button"
                                class="text-gray-500 transition-all duration-300 ease-in-out rounded-full hover:text-red-700"
                                @click="
                                    selectedMotif = null;
                                    $event.stopPropagation();
                                "
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    class="size-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </template>
                    </Chip>
                    <ColorChip
                        v-if="selectedColor"
                        :label="selectedColor.name"
                        :hexCode="selectedColor.hex_code"
                        :selected="true"
                        class="pr-2 !gap-1 !cursor-default text-sm"
                        radioClasses="!size-4 mr-0.5"
                    >
                        <template #suffix>
                            <button
                                type="button"
                                class="text-gray-500 transition-all duration-300 ease-in-out rounded-full hover:text-red-700"
                                @click="
                                    selectedColor = null;
                                    $event.stopPropagation();
                                "
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    class="size-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </template>
                    </ColorChip>
                </div>
                <button
                    type="button"
                    class="text-sm text-red-700 hover:underline w-fit"
                    @click="
                        selectedMotif = null;
                        selectedColor = null;
                    "
                >
                    Hapus Filter
                </button>
            </div>
        </div>

        <!-- List -->
        <div
            v-if="props.isLoading"
            class="flex items-center justify-center w-full h-[40vh]"
        >
            <div class="circular-loading"></div>
        </div>
        <div v-else-if="props.variants.length == 0" class="w-full">
            <div
                class="flex flex-col gap-4 items-center justify-center h-[40vh] w-full"
            >
                <p class="text-sm text-center text-gray-500">
                    Belum ada variasi produk. <br />
                    Silakan tambahkan variasi produk terlebih dahulu.
                </p>
                <PrimaryButton
                    type="button"
                    class="text-nowrap"
                    :disabled="props.isLoading"
                    @click="openAddVariantForm"
                >
                    Tambah Variasi
                </PrimaryButton>
            </div>
        </div>
        <div
            v-else-if="filteredVariants.length > 0"
            class="grid w-full grid-cols-1 gap-2 mt-1.5"
        >
            <div
                v-for="(variant, index) in filteredVariants"
                :key="index"
                class="w-full"
            >
                <div
                    v-if="
                        index == 0 ||
                        variant.motif != filteredVariants[index - 1].motif ||
                        variant.color_id != filteredVariants[index - 1].color_id
                    "
                    class="mb-2 flex flex-col items-start gap-0.5"
                    :class="{
                        'mt-1.5 ': index > 0,
                    }"
                >
                    <p
                        v-if="
                            index == 0 ||
                            variant.motif != filteredVariants[index - 1].motif
                        "
                        class="font-medium text-gray-500"
                    >
                        {{ variant.motif }}
                    </p>
                    <p
                        v-if="
                            index == 0 ||
                            variant.motif !=
                                filteredVariants[index - 1].motif ||
                            variant.color_id !=
                                filteredVariants[index - 1].color_id
                        "
                        class="flex items-center text-sm font-medium text-gray-500"
                    >
                        {{ variant.color?.name }}
                    </p>
                </div>
                <VariantCard
                    :name="getVariantName(variant)"
                    :variant="variant"
                    :index="index"
                    @click="variant.showEditForm = true"
                    @delete="variant.showDeleteConfirmation = true"
                />
                <Modal
                    :show="variant.showEditForm"
                    @close="variant.showEditForm = false"
                >
                    <VariantForm
                        :product="props.product"
                        :variant="variant"
                        @close="variant.showEditForm = false"
                        @submitted="
                            variant.showEditForm = false;
                            emit('onUpdated', $event);
                        "
                    />
                </Modal>
                <DeleteConfirmationDialog
                    :title="`Hapus Varian Produk <b>${variant.motif} - ${variant.color?.name} - ${variant.size?.name}</b>?`"
                    :show="variant.showDeleteConfirmation"
                    @close="variant.showDeleteConfirmation = false"
                    @delete="
                        variant.showDeleteConfirmation = false;
                        emit('onDelete', variant);
                    "
                />
            </div>
        </div>
        <div v-else class="flex items-center justify-center h-[40vh] w-full">
            <p class="text-sm text-center text-gray-500">
                Data tidak ditemukan.
            </p>
        </div>

        <Modal :show="showAddVariantForm" @close="showAddVariantForm = false">
            <VariantForm
                :product="props.product"
                :variant="null"
                @close="showAddVariantForm = false"
                @submitted="
                    showAddVariantForm = false;
                    emit('onCreated', $event);
                "
            />
        </Modal>
    </div>
</template>

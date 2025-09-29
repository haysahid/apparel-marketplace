<script setup>
import { ref, onMounted, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import Dropdown from "@/Components/Dropdown.vue";
import TextInput from "@/Components/TextInput.vue";
import SalesReportTable from "./Report/SalesReportTable.vue";
import PurchaseReportTable from "./Report/PurchaseReportTable.vue";
import axios from "axios";
import StockReportTable from "./Report/StockReportTable.vue";
import LoadingDialog from "@/Components/LoadingDialog.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import cookieManager from "@/plugins/cookie-manager";

const props = defineProps({
    brands: null,

    title: null,
    report_type: null,
    start_date: null,
    end_date: null,
    printed_at: null,
    report: null,
    totals: null,
    brand: null,
});

const selectedReportType = ref({ label: "Penjualan", value: "sale" });
const reportTypes = [
    { label: "Penjualan", value: "sale" },
    { label: "Stok", value: "stock" },
];
const reportTypeSearch = ref("");
const isReportTypeDropdownOpen = ref(false);

const filteredReportTypes = computed(() => {
    return reportTypes.filter((type) =>
        type.label.toLowerCase().includes(reportTypeSearch.value.toLowerCase())
    );
});

const selectedBrand = ref(null);
const brands = props.brands || [];
const brandSearch = ref("");
const isBrandDropdownOpen = ref(false);
const filteredBrands = computed(() => {
    return brands.filter((brand) =>
        brand.name.toLowerCase().includes(brandSearch.value.toLowerCase())
    );
});

const startDate = ref(null);
const endDate = ref(null);

const startDateInput = ref(null);
const endDateInput = ref(null);

const isStockReport = computed(() => {
    return selectedReportType.value.value === "stock";
});

const showSuccessDialog = ref(false);
const successMessage = ref("Berhasil");

const openSuccessDialog = (message) => {
    successMessage.value = message;
    showSuccessDialog.value = true;
};

const showErrorDialog = ref(false);
const errorMessage = ref("");

const openErrorDialog = (message) => {
    errorMessage.value = message;
    showErrorDialog.value = true;
};

const page = usePage();

const printStatus = ref(null);

function isMobileDevice() {
    return /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
}

function printReport() {
    printStatus.value = "loading";

    const token = `Bearer ${cookieManager.getItem("access_token")}`;

    const reportType = selectedReportType.value.value;
    const brand = selectedBrand.value ? selectedBrand.value.name : undefined;
    const start = startDate.value ? startDate.value : undefined;
    const end = endDate.value ? endDate.value : undefined;

    axios
        .post(
            `/api/my-store/report/generate`,
            {
                report_type: reportType,
                brand: brand,
                start_date: start,
                end_date: end,
            },
            {
                headers: {
                    Authorization: token,
                    Accept: "application/pdf",
                },
            }
        )
        .then((response) => {
            if (response.data && response.data.result.pdf) {
                printStatus.value = "success";

                try {
                    // Create blob from base64
                    const byteCharacters = atob(response.data.result.pdf);
                    const byteArrays = [];

                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteArrays.push(byteCharacters.charCodeAt(i));
                    }

                    const byteArray = new Uint8Array(byteArrays);

                    const url = window.URL.createObjectURL(
                        new Blob([byteArray], { type: "application/pdf" })
                    );

                    // Remove iframe if exists
                    const oldIframe = document.querySelector("iframe");
                    if (oldIframe) {
                        oldIframe.remove();
                    }

                    if (isMobileDevice()) {
                        // For mobile devices, open in new tab
                        window.open(url, "_blank");
                        return;
                    } else {
                        // Create iframe
                        const iframe = document.createElement("iframe");
                        iframe.style.display = "none";
                        iframe.src = url;
                        document.body.appendChild(iframe);

                        // Print when iframe is loaded
                        iframe.onload = function () {
                            iframe.contentWindow.print();
                        };
                    }

                    // Revoke object URL after printing
                    window.URL.revokeObjectURL(url);
                } catch (error) {
                    printStatus.value = "error";
                    openErrorDialog("Gagal mengunduh laporan.");
                }
            } else {
                printStatus.value = "error";
                openErrorDialog("Gagal mengunduh laporan.");
            }
        })
        .catch((error) => {
            printStatus.value = "error";
            openErrorDialog(
                error.response?.data?.message ||
                    "Terjadi kesalahan saat mengunduh laporan."
            );
        });
}

onMounted(() => {
    if (page.props.flash.success) {
        openSuccessDialog(page.props.flash.success);
    }

    selectedReportType.value = route().params.report_type
        ? reportTypes.find(
              (type) => type.value === route().params.report_type
          ) || selectedReportType.value
        : selectedReportType.value;
    selectedBrand.value = route().params.brand
        ? brands.find((brand) => brand.name === route().params.brand) ||
          selectedBrand.value
        : selectedBrand.value;
    startDate.value = route().params.start_date || null;
    endDate.value = route().params.end_date || null;
});
</script>

<template>
    <MyStoreLayout title="Laporan" :showTitle="true">
        <div>
            <DefaultCard
                :isMain="true"
                class="flex items-center justify-between gap-4 print:hidden"
            >
                <PrimaryButton
                    type="button"
                    :disabled="printStatus === 'loading'"
                    @click="printReport()"
                >
                    Cetak Laporan
                </PrimaryButton>
                <div class="flex items-center gap-2">
                    <!-- Report Type -->
                    <DropdownSearchInput
                        v-if="reportTypes"
                        id="report_type"
                        :modelValue="selectedReportType"
                        :options="filteredReportTypes"
                        placeholder="Jenis Laporan"
                        class="max-w-48"
                        :required="true"
                        @update:modelValue="
                            (option) => {
                                selectedReportType = option;

                                if (selectedReportType.value === 'stock') {
                                    startDate = null;
                                    endDate = null;
                                }

                                $inertia.get(route('my-store.report'), {
                                    report_type: selectedReportType.value,
                                    brand: selectedBrand
                                        ? selectedBrand.name
                                        : undefined,
                                    start_date: startDate
                                        ? startDate
                                        : undefined,
                                    end_date: endDate ? endDate : undefined,
                                });
                            }
                        "
                    />

                    <!-- Brand -->
                    <DropdownSearchInput
                        v-if="isStockReport && brands"
                        id="brand"
                        :modelValue="
                            selectedBrand
                                ? {
                                      label: selectedBrand.name,
                                      value: selectedBrand.id,
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
                        class="max-w-48"
                        @update:modelValue="
                            (option) => {
                                selectedBrand = option
                                    ? brands.find((b) => b.id === option.value)
                                    : null;

                                $inertia.get(route('my-store.report'), {
                                    report_type: selectedReportType.value,
                                    brand: selectedBrand
                                        ? selectedBrand.name
                                        : undefined,
                                });
                            }
                        "
                        @clear="
                            selectedBrand = null;
                            $inertia.get(route('my-store.report'), {
                                report_type: selectedReportType.value,
                                brand: selectedBrand
                                    ? selectedBrand.name
                                    : undefined,
                            });
                        "
                    />

                    <template v-if="!isStockReport">
                        <!-- Start Date -->
                        <TextInput
                            ref="startDateInput"
                            v-model="startDate"
                            type="date"
                            placeholder="Tanggal Mulai"
                            :disabled="isStockReport"
                            @change="
                                $inertia.get(route('my-store.report'), {
                                    report_type: selectedReportType.value,
                                    start_date: startDate
                                        ? startDate
                                        : undefined,
                                    end_date: endDate ? endDate : undefined,
                                })
                            "
                        >
                            <template #suffix>
                                <div class="absolute right-1.5">
                                    <button
                                        type="button"
                                        class="p-2"
                                        :disabled="isStockReport"
                                        @click="
                                            startDateInput.input.showPicker()
                                        "
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            class="size-4 fill-gray-400"
                                        >
                                            <path
                                                d="M8 14C7.71667 14 7.47933 13.904 7.288 13.712C7.09667 13.52 7.00067 13.2827 7 13C6.99933 12.7173 7.09533 12.48 7.288 12.288C7.48067 12.096 7.718 12 8 12C8.282 12 8.51967 12.096 8.713 12.288C8.90633 12.48 9.002 12.7173 9 13C8.998 13.2827 8.902 13.5203 8.712 13.713C8.522 13.9057 8.28467 14.0013 8 14ZM12 14C11.7167 14 11.4793 13.904 11.288 13.712C11.0967 13.52 11.0007 13.2827 11 13C10.9993 12.7173 11.0953 12.48 11.288 12.288C11.4807 12.096 11.718 12 12 12C12.282 12 12.5197 12.096 12.713 12.288C12.9063 12.48 13.002 12.7173 13 13C12.998 13.2827 12.902 13.5203 12.712 13.713C12.522 13.9057 12.2847 14.0013 12 14ZM16 14C15.7167 14 15.4793 13.904 15.288 13.712C15.0967 13.52 15.0007 13.2827 15 13C14.9993 12.7173 15.0953 12.48 15.288 12.288C15.4807 12.096 15.718 12 16 12C16.282 12 16.5197 12.096 16.713 12.288C16.9063 12.48 17.002 12.7173 17 13C16.998 13.2827 16.902 13.5203 16.712 13.713C16.522 13.9057 16.2847 14.0013 16 14ZM5 22C4.45 22 3.97933 21.8043 3.588 21.413C3.19667 21.0217 3.00067 20.5507 3 20V6C3 5.45 3.196 4.97934 3.588 4.588C3.98 4.19667 4.45067 4.00067 5 4H6V3C6 2.71667 6.096 2.47934 6.288 2.288C6.48 2.09667 6.71733 2.00067 7 2C7.28267 1.99934 7.52033 2.09534 7.713 2.288C7.90567 2.48067 8.00133 2.718 8 3V4H16V3C16 2.71667 16.096 2.47934 16.288 2.288C16.48 2.09667 16.7173 2.00067 17 2C17.2827 1.99934 17.5203 2.09534 17.713 2.288C17.9057 2.48067 18.0013 2.718 18 3V4H19C19.55 4 20.021 4.196 20.413 4.588C20.805 4.98 21.0007 5.45067 21 6V20C21 20.55 20.8043 21.021 20.413 21.413C20.0217 21.805 19.5507 22.0007 19 22H5ZM5 20H19V10H5V20Z"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </TextInput>

                        -

                        <!-- End Date -->
                        <TextInput
                            ref="endDateInput"
                            v-model="endDate"
                            type="date"
                            placeholder="Tanggal Selesai"
                            :disabled="isStockReport"
                            @change="
                                $inertia.get(route('my-store.report'), {
                                    report_type: selectedReportType.value,
                                    start_date: startDate
                                        ? startDate
                                        : undefined,
                                    end_date: endDate ? endDate : undefined,
                                })
                            "
                        >
                            <template #suffix>
                                <button
                                    type="button"
                                    class="absolute p-2 right-1.5"
                                    :disabled="isStockReport"
                                    @click="endDateInput.input.showPicker()"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        class="size-4 fill-gray-400"
                                    >
                                        <path
                                            d="M8 14C7.71667 14 7.47933 13.904 7.288 13.712C7.09667 13.52 7.00067 13.2827 7 13C6.99933 12.7173 7.09533 12.48 7.288 12.288C7.48067 12.096 7.718 12 8 12C8.282 12 8.51967 12.096 8.713 12.288C8.90633 12.48 9.002 12.7173 9 13C8.998 13.2827 8.902 13.5203 8.712 13.713C8.522 13.9057 8.28467 14.0013 8 14ZM12 14C11.7167 14 11.4793 13.904 11.288 13.712C11.0967 13.52 11.0007 13.2827 11 13C10.9993 12.7173 11.0953 12.48 11.288 12.288C11.4807 12.096 11.718 12 12 12C12.282 12 12.5197 12.096 12.713 12.288C12.9063 12.48 13.002 12.7173 13 13C12.998 13.2827 12.902 13.5203 12.712 13.713C12.522 13.9057 12.2847 14.0013 12 14ZM16 14C15.7167 14 15.4793 13.904 15.288 13.712C15.0967 13.52 15.0007 13.2827 15 13C14.9993 12.7173 15.0953 12.48 15.288 12.288C15.4807 12.096 15.718 12 16 12C16.282 12 16.5197 12.096 16.713 12.288C16.9063 12.48 17.002 12.7173 17 13C16.998 13.2827 16.902 13.5203 16.712 13.713C16.522 13.9057 16.2847 14.0013 16 14ZM5 22C4.45 22 3.97933 21.8043 3.588 21.413C3.19667 21.0217 3.00067 20.5507 3 20V6C3 5.45 3.196 4.97934 3.588 4.588C3.98 4.19667 4.45067 4.00067 5 4H6V3C6 2.71667 6.096 2.47934 6.288 2.288C6.48 2.09667 6.71733 2.00067 7 2C7.28267 1.99934 7.52033 2.09534 7.713 2.288C7.90567 2.48067 8.00133 2.718 8 3V4H16V3C16 2.71667 16.096 2.47934 16.288 2.288C16.48 2.09667 16.7173 2.00067 17 2C17.2827 1.99934 17.5203 2.09534 17.713 2.288C17.9057 2.48067 18.0013 2.718 18 3V4H19C19.55 4 20.021 4.196 20.413 4.588C20.805 4.98 21.0007 5.45067 21 6V20C21 20.55 20.8043 21.021 20.413 21.413C20.0217 21.805 19.5507 22.0007 19 22H5ZM5 20H19V10H5V20Z"
                                        />
                                    </svg>
                                </button>
                            </template>
                        </TextInput>
                    </template>
                </div>
            </DefaultCard>

            <div
                class="mt-6 overflow-x-auto print:!overflow-x-visible bg-gray-100 p-4 sm:p-6 print:!p-0 print:bg-transparent rounded-lg print:rounded-none h-[74vh] print:h-auto overflow-y-auto border border-1 border-gray-200 -border-offset-1 print:border-none"
            >
                <div
                    class="w-[21cm] mx-auto border border-gray-200 shadow-sm p-9 bg-white print:!bg-transparent print:!p-0 print:shadow-none print:border-none min-h-[29.7cm] print:min-h-0 scale-50 sm:scale-75 md:scale-100 print:scale-100 origin-top-left"
                >
                    <div class="mb-4 text-center">
                        <h1 class="text-lg font-bold">{{ title }}</h1>
                        <p class="text-xs">
                            Periode {{ props.start_date ?? "awal" }} sampai
                            {{ props.end_date ?? "akhir" }}
                        </p>
                    </div>

                    <div class="mb-2">
                        <p class="text-xs">
                            Dicetak pada:
                            {{
                                props.printed_at ?? new Date().toLocaleString()
                            }}
                        </p>
                    </div>

                    <SalesReportTable
                        v-if="selectedReportType.value == 'sale'"
                        :salesReport="props.report"
                        :totals="props.totals"
                    />
                    <PurchaseReportTable
                        v-else-if="selectedReportType.value == 'purchase'"
                        :purchaseReport="props.report"
                        :totals="props.totals"
                    />
                    <StockReportTable
                        v-else-if="selectedReportType.value == 'stock'"
                        :stockReport="props.report"
                        :totals="props.totals"
                    />
                </div>
            </div>

            <LoadingDialog
                :show="printStatus === 'loading'"
                title="Memproses laporan..."
            />

            <SuccessDialog
                :show="showSuccessDialog"
                :title="successMessage"
                @close="showSuccessDialog = false"
            />

            <ErrorDialog
                :show="showErrorDialog"
                @close="showErrorDialog = false"
            >
                <template #content>
                    <div>
                        <div
                            class="mb-1 text-lg font-medium text-center text-gray-900"
                        >
                            Terjadi Kesalahan
                        </div>
                        <p class="text-center text-gray-700">
                            {{ errorMessage }}
                        </p>
                    </div>
                </template>
            </ErrorDialog>
        </div>
    </MyStoreLayout>
</template>

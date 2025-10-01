<script setup lang="ts">
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import InputGroup from "@/Components/InputGroup.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import InfoTooltip from "@/Components/InfoTooltip.vue";
import DateInput from "@/Components/DateInput.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import Stepper from "@/Components/Stepper.vue";
import VoucherPreview from "./VoucherPreview.vue";
import Checkbox from "@/Components/Checkbox.vue";
import Switch from "@/Components/Switch.vue";

const props = defineProps({
    voucher: {
        type: Object,
        default: null,
    },
});

const form = useForm(
    props.voucher
        ? {
              ...props.voucher,
              redeem_start_date: props.voucher.redeem_start_date
                  ? props.voucher.redeem_start_date.split(" ")[0]
                  : null,
              redeem_end_date: props.voucher.redeem_end_date
                  ? props.voucher.redeem_end_date.split(" ")[0]
                  : null,
              usage_start_date: props.voucher.usage_start_date
                  ? props.voucher.usage_start_date.split(" ")[0]
                  : null,
              usage_end_date: props.voucher.usage_end_date
                  ? props.voucher.usage_end_date.split(" ")[0]
                  : null,

              // Utility
              usage_period_type: props.voucher.usage_duration_days
                  ? "days"
                  : props.voucher.usage_start_date ||
                    props.voucher.usage_end_date
                  ? "range"
                  : "days", // days, range
          }
        : {
              name: null,
              code: null,
              description: null,
              type: "percentage",
              amount: null,
              min_amount: null,
              max_amount: null,
              redeem_start_date: null,
              redeem_end_date: null,
              usage_duration_days: null,
              usage_start_date: null,
              usage_end_date: null,
              usage_limit: null,
              required_points: null,
              usage_url: null,
              is_public: false,
              is_internal: true,
              partner_id: null,

              // Utility
              usage_period_type: "range", // days, range
          }
);

const typeSearch = ref(null);
const types = [
    { label: "Persentase", value: "percentage" },
    { label: "Nominal", value: "fixed" },
];
const filteredTypes = computed(() => {
    if (!typeSearch.value) return types;
    return types.filter((type) =>
        type.label.toLowerCase().includes(typeSearch.value.toLowerCase())
    );
});

const submit = () => {
    if (props.voucher?.id) {
        form.transform((data) => {
            const formData = new FormData();
            Object.keys(data).forEach((key) => {
                if (data[key] === null || data[key] === undefined) {
                    return;
                }

                formData.append(key, data[key]);
            });
            return formData;
        }).post(
            route("my-store.voucher.update", {
                voucher: props.voucher,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
            }
        );
    } else {
        form.post(route("my-store.voucher.store"), {
            onError: (errors) => {
                openErrorDialog(errors.error);
            },
        });
    }
};

const showErrorDialog = ref(false);
const errorMessage = ref(null);

const openErrorDialog = (message) => {
    errorMessage.value = message;
    showErrorDialog.value = true;
};

const stepIndex = ref(0);

const steps = computed(() => [
    {
        title: "Informasi Voucher",
        subtitle: null,
        disabled: false,
    },
    {
        title: "Pengaturan Lanjutan",
        subtitle: null,
        disabled: !form.name || !form.code || !form.type || !form.amount,
    },
]);

const scrollToTop = () => {
    const productListElement = document.querySelector("#main-area");
    if (productListElement) {
        productListElement.scrollTo({ top: 0, behavior: "smooth" });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="">
        <div class="flex flex-col sm:gap-4">
            <!-- Step Navigation -->
            <DefaultCard
                :isMain="true"
                class="!py-0 !px-4 sticky top-0 z-10 sm:static"
            >
                <Stepper :steps="steps" v-model:stepIndex="stepIndex" />
            </DefaultCard>

            <div class="flex sm:gap-4">
                <!-- Form -->
                <DefaultCard :isMain="true" class="w-full">
                    <div class="flex flex-col items-start gap-4">
                        <div
                            class="flex flex-col w-full gap-y-4 gap-x-6 sm:flex-row"
                        >
                            <!-- Step 1 -->
                            <div
                                v-if="stepIndex === 0"
                                class="flex flex-col w-full max-w-3xl gap-4"
                            >
                                <!-- Name -->
                                <InputGroup id="name" label="Nama Voucher">
                                    <TextInput
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Masukkan Nama Voucher"
                                        required
                                        :autofocus="true"
                                        :error="form.errors.name"
                                        @update:modelValue="
                                            form.errors.name = null
                                        "
                                    />
                                </InputGroup>

                                <!-- Code -->
                                <InputGroup id="code" label="Kode Voucher">
                                    <TextInput
                                        id="code"
                                        v-model="form.code"
                                        type="text"
                                        placeholder="Masukkan Kode Voucher"
                                        required
                                        :error="form.errors.code"
                                        @update:modelValue="
                                            form.errors.code = null
                                        "
                                    />
                                </InputGroup>

                                <!-- Description -->
                                <InputGroup
                                    id="description"
                                    label="Deskripsi Voucher"
                                >
                                    <TextAreaInput
                                        id="description"
                                        v-model="form.description"
                                        type="text"
                                        placeholder="Masukkan Deskripsi"
                                        autocomplete="description"
                                        :error="form.errors.description"
                                        @update:modelValue="
                                            form.errors.description = null
                                        "
                                    />
                                </InputGroup>

                                <div class="flex items-center w-full gap-4">
                                    <!-- Type -->
                                    <InputGroup id="type" label="Tipe Voucher">
                                        <DropdownSearchInput
                                            id="type"
                                            :modelValue="
                                                form.type
                                                    ? {
                                                          label:
                                                              form.type ===
                                                              'percentage'
                                                                  ? 'Persentase'
                                                                  : 'Nominal',
                                                          value: form.type,
                                                      }
                                                    : null
                                            "
                                            :options="filteredTypes"
                                            placeholder="Pilih Tipe Voucher"
                                            required
                                            @update:modelValue="
                                                (option) => {
                                                    form.type = option?.value;
                                                }
                                            "
                                            @clear="form.type = null"
                                        />
                                    </InputGroup>

                                    <!-- Amount -->
                                    <InputGroup
                                        id="amount"
                                        :label="`Nilai Voucher (${
                                            form.type === 'percentage'
                                                ? '%'
                                                : 'Rp'
                                        })`"
                                    >
                                        <TextInput
                                            id="amount"
                                            v-model="form.amount"
                                            type="number"
                                            placeholder="Masukkan Nilai Voucher"
                                            required
                                            :error="form.errors.amount"
                                            @update:modelValue="
                                                form.errors.amount = null
                                            "
                                        />
                                    </InputGroup>
                                </div>

                                <div class="flex items-center w-full gap-4">
                                    <!-- Start Date -->
                                    <InputGroup
                                        id="redeem_start_date"
                                        label="Tgl. Mulai"
                                    >
                                        <DateInput
                                            v-model="form.redeem_start_date"
                                            placeholder="Tanggal Mulai"
                                            :error="
                                                form.errors.redeem_start_date
                                            "
                                            @update:modelValue="
                                                form.errors.redeem_start_date =
                                                    null
                                            "
                                        />
                                        <template #suffix>
                                            <InfoTooltip
                                                id="redeem-start-date-tooltip"
                                                text="Tanggal mulai voucher dapat diperoleh. Biarkan kosong untuk langsung dapat diperoleh."
                                            />
                                        </template>
                                    </InputGroup>

                                    <!-- End Date -->
                                    <InputGroup
                                        id="redeem_end_date"
                                        label="Tgl. Berakhir"
                                    >
                                        <DateInput
                                            v-model="form.redeem_end_date"
                                            placeholder="Tanggal Berakhir"
                                            :error="form.errors.redeem_end_date"
                                            @update:modelValue="
                                                form.errors.redeem_end_date =
                                                    null
                                            "
                                        />
                                        <template #suffix>
                                            <InfoTooltip
                                                id="redeem-end-date-tooltip"
                                                text="Tanggal berakhir voucher dapat diperoleh. Biarkan kosong untuk tidak ada batasan tanggal berakhir penukaran."
                                            />
                                        </template>
                                    </InputGroup>
                                </div>

                                <!-- Required Points -->
                                <InputGroup
                                    id="required_points"
                                    label="Poin Penukaran"
                                >
                                    <TextInput
                                        id="required_points"
                                        v-model="form.required_points"
                                        type="number"
                                        placeholder="Masukkan Poin Penukaran"
                                        :error="form.errors.required_points"
                                        @update:modelValue="
                                            form.errors.required_points = null
                                        "
                                    />
                                    <template #suffix>
                                        <InfoTooltip
                                            id="required-points-tooltip"
                                            text="Jumlah poin yang harus dimiliki pelanggan untuk dapat ditukar dengan voucher ini."
                                        />
                                    </template>
                                </InputGroup>

                                <!-- Is Public -->
                                <InputGroup id="is_public" label="Akses Publik">
                                    <Switch
                                        id="is_public"
                                        v-model="form.is_public"
                                    />
                                    <template #suffix>
                                        <InfoTooltip
                                            id="is-public-tooltip"
                                            text="Tandai jika voucher ini dapat diakses oleh publik."
                                        />
                                    </template>
                                </InputGroup>
                            </div>

                            <!-- Step 2 -->
                            <div
                                v-if="stepIndex === 1"
                                class="flex flex-col w-full max-w-3xl gap-4"
                            >
                                <!-- Usage Limit -->
                                <InputGroup
                                    id="usage_limit"
                                    label="Batas Penggunaan"
                                >
                                    <TextInput
                                        id="usage_limit"
                                        v-model="form.usage_limit"
                                        type="number"
                                        placeholder="Masukkan Batas Penggunaan"
                                        :error="form.errors.usage_limit"
                                        @update:modelValue="
                                            form.errors.usage_limit = null
                                        "
                                    />
                                    <template #suffix>
                                        <InfoTooltip
                                            id="usage-limit-tooltip"
                                            text="Jumlah maksimal penggunaan voucher per pelanggan. Biarkan kosong untuk tanpa batas."
                                        />
                                    </template>
                                </InputGroup>

                                <div class="flex items-center w-full gap-4">
                                    <!-- Min. Amount -->
                                    <InputGroup
                                        id="min_amount"
                                        label="Min. Diskon (Rp)"
                                    >
                                        <TextInput
                                            id="min_amount"
                                            v-model="form.min_amount"
                                            type="number"
                                            placeholder="Masukkan Min. Diskon"
                                            :error="form.errors.min_amount"
                                            @update:modelValue="
                                                form.errors.min_amount = null
                                            "
                                        />
                                        <template #suffix>
                                            <InfoTooltip
                                                id="min-amount-tooltip"
                                                text="Minimal potongan yang dapat diberikan oleh voucher."
                                            />
                                        </template>
                                    </InputGroup>

                                    <!-- Max. Amount -->
                                    <InputGroup
                                        id="max_amount"
                                        label="Maks. Diskon (Rp)"
                                    >
                                        <TextInput
                                            id="max_amount"
                                            v-model="form.max_amount"
                                            type="number"
                                            placeholder="Masukkan Maks. Diskon"
                                            :error="form.errors.max_amount"
                                            @update:modelValue="
                                                form.errors.max_amount = null
                                            "
                                        />
                                        <template #suffix>
                                            <InfoTooltip
                                                id="max-amount-tooltip"
                                                text="Maksimal potongan yang dapat diberikan oleh voucher."
                                            />
                                        </template>
                                    </InputGroup>
                                </div>

                                <!-- Usage Period Type -->
                                <InputGroup
                                    id="usage_period_type"
                                    label="Jenis Periode Penggunaan"
                                >
                                    <DropdownSearchInput
                                        id="usage_period_type"
                                        label="Jenis Periode Penggunaan"
                                        :modelValue="
                                            form.usage_period_type
                                                ? {
                                                      label:
                                                          form.usage_period_type ===
                                                          'days'
                                                              ? 'Hitungan Hari'
                                                              : 'Rentang Tanggal',
                                                      value: form.usage_period_type,
                                                  }
                                                : null
                                        "
                                        :options="[
                                            {
                                                label: 'Hitungan Hari',
                                                value: 'days',
                                            },
                                            {
                                                label: 'Rentang Tanggal',
                                                value: 'range',
                                            },
                                        ]"
                                        placeholder="Pilih Jenis Periode Penggunaan"
                                        required
                                        @update:modelValue="
                                            (option) => {
                                                form.usage_period_type =
                                                    option?.value;
                                                // Reset usage date fields
                                                form.usage_duration_days = null;
                                                form.usage_start_date = null;
                                                form.usage_end_date = null;
                                            }
                                        "
                                        @clear="form.usage_period_type = null"
                                    />
                                </InputGroup>

                                <!-- Usage Duration Days -->
                                <InputGroup
                                    v-if="form.usage_period_type === 'days'"
                                    id="usage_duration_days"
                                    label="Durasi Penggunaan (Hari)"
                                >
                                    <TextInput
                                        id="usage_duration_days"
                                        v-model="form.usage_duration_days"
                                        type="number"
                                        placeholder="Masukkan Durasi Penggunaan"
                                        :error="form.errors.usage_duration_days"
                                        @update:modelValue="
                                            form.errors.usage_duration_days =
                                                null
                                        "
                                    />
                                    <template #suffix>
                                        <InfoTooltip
                                            id="usage-duration-days-tooltip"
                                            text="Jumlah hari voucher dapat digunakan setelah diperoleh. Biarkan kosong untuk tanpa batas."
                                        />
                                    </template>
                                </InputGroup>

                                <div
                                    v-else-if="
                                        form.usage_period_type === 'range'
                                    "
                                    class="flex items-center w-full gap-4"
                                >
                                    <!-- Usage Start Date -->
                                    <InputGroup
                                        id="usage_start_date"
                                        label="Tgl. Mulai Penggunaan"
                                    >
                                        <DateInput
                                            id="usage_start_date"
                                            v-model="form.usage_start_date"
                                            placeholder="Tanggal Mulai Penggunaan"
                                            :error="
                                                form.errors.usage_start_date
                                            "
                                            @update:modelValue="
                                                form.errors.usage_start_date =
                                                    null
                                            "
                                        />
                                        <template #suffix>
                                            <InfoTooltip
                                                id="usage-start-date-tooltip"
                                                text="Tanggal mulai voucher dapat digunakan. Biarkan kosong untuk langsung dapat digunakan setelah diperoleh."
                                            />
                                        </template>
                                    </InputGroup>

                                    <!-- Usage End Date -->
                                    <InputGroup
                                        id="usage_end_date"
                                        label="Tgl. Akhir Penggunaan"
                                    >
                                        <DateInput
                                            id="usage_end_date"
                                            v-model="form.usage_end_date"
                                            placeholder="Tanggal Akhir Penggunaan"
                                            :error="form.errors.usage_end_date"
                                            @update:modelValue="
                                                form.errors.usage_end_date =
                                                    null
                                            "
                                        />
                                        <template #suffix>
                                            <InfoTooltip
                                                id="usage-end-date-tooltip"
                                                text="Tanggal berakhir voucher dapat digunakan. Biarkan kosong untuk tidak ada batasan tanggal berakhir penggunaan."
                                            />
                                        </template>
                                    </InputGroup>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 mt-4">
                            <SecondaryButton
                                v-if="stepIndex > 0"
                                type="button"
                                @click="
                                    if (stepIndex > 0) {
                                        stepIndex--;
                                        scrollToTop();
                                    } else {
                                        $inertia.visit(
                                            route('my-store.voucher')
                                        );
                                    }
                                "
                            >
                                Kembali
                            </SecondaryButton>
                            <PrimaryButton
                                v-if="stepIndex < steps.length - 1"
                                @click="
                                    stepIndex++;
                                    scrollToTop();
                                "
                                type="button"
                                :disabled="steps[stepIndex + 1].disabled"
                            >
                                Selanjutnya
                            </PrimaryButton>
                            <PrimaryButton v-else type="submit">
                                Simpan
                            </PrimaryButton>
                        </div>
                    </div>
                </DefaultCard>

                <!-- Preview -->
                <DefaultCard
                    :isMain="true"
                    class="hidden w-full max-w-sm h-fit lg:block"
                >
                    <VoucherPreview :voucher="(form.data() as VoucherEntity)" />
                </DefaultCard>
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

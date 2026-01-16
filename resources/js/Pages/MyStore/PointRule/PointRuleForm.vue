<script setup lang="ts">
import { nextTick, onMounted, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import InputGroup from "@/Components/InputGroup.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import InfoTooltip from "@/Components/InfoTooltip.vue";
import DateInput from "@/Components/DateInput.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import { goBack } from "@/plugins/helpers";

const props = defineProps({
    pointRule: {
        type: Object,
        default: null,
    },
    isDialog: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["onSubmitted", "close"]);

const types = [
    { value: "per_amount_spent", label: "Per Nominal Belanja" },
    { value: "per_transaction", label: "Per Transaksi" },
    { value: "on_signup", label: "Pendaftaran" },
    { value: "on_review", label: "Ulasan" },
    { value: "on_referral", label: "Rujukan" },
    { value: "on_birthday", label: "Ulang Tahun" },
    { value: "on_anniversary", label: "Ulang Tahun Toko" },
    { value: "other", label: "Lainnya" },
];

const form = useForm(
    props.pointRule
        ? {
              ...props.pointRule,
              valid_from: props.pointRule.valid_from?.split(" ")[0] ?? null,
              valid_until: props.pointRule.valid_until?.split(" ")[0] ?? null,
          }
        : {
              name: null,
              description: null,
              type: null,
              min_spend: null,
              points_earned: null,
              conversion_rate: null,
              valid_from: null,
              valid_until: null,
          }
);

const submit = () => {
    if (props.pointRule?.id) {
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
            route("my-store.point-rule.update", {
                pointRule: props.pointRule,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
                onFinish: () => {
                    form.reset();
                },
            }
        );
    } else {
        form.transform((data) => {
            return {
                ...data,
                is_dialog: props.isDialog ? 1 : 0,
            };
        }).post(route("my-store.point-rule.store"), {
            preserveScroll: props.isDialog,
            preserveState: props.isDialog,
            onError: (errors) => {
                openErrorDialog(errors.error);
            },
            onSuccess: () => {
                if (props.isDialog) emit("onSubmitted", form.name);
                form.reset();
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

onMounted(() => {
    nextTick(() => {
        const input = document.getElementById(
            "point-rule-name"
        ) as HTMLInputElement;
        input?.focus();
    });
});
</script>

<template>
    <form @submit.prevent="submit" class="max-w-3xl">
        <div class="flex flex-col items-start gap-4">
            <!-- Name -->
            <InputGroup for="point-rule-name" label="Nama Aturan Poin">
                <TextInput
                    id="point-rule-name"
                    v-model="form.name"
                    type="text"
                    placeholder="Masukkan Nama Aturan Poin"
                    required
                    :autofocus="true"
                    :error="form.errors.name"
                    @update:modelValue="form.errors.name = null"
                />
            </InputGroup>

            <!-- Description -->
            <InputGroup for="description" label="Deskripsi (Opsional)">
                <TextAreaInput
                    id="description"
                    v-model="form.description"
                    placeholder="Masukkan Deskripsi Aturan Poin"
                    :error="form.errors.description"
                    @update:modelValue="form.errors.description = null"
                />
            </InputGroup>

            <div class="flex items-center w-full gap-4">
                <!-- Type -->
                <InputGroup for="type" label="Jenis Aturan Poin">
                    <DropdownSearchInput
                        id="type"
                        :modelValue="
                            form.type
                                ? types.find((type) => type.value === form.type)
                                : null
                        "
                        :options="types"
                        :searchable="true"
                        placeholder="Pilih Jenis Aturan Poin"
                        required
                        :error="form.errors.type"
                        @update:modelValue="
                            (option) => {
                                form.type = option.value;
                                form.errors.type = null;
                            }
                        "
                    />
                </InputGroup>

                <!-- Points Earned -->
                <InputGroup for="points-earned" label="Poin yang Didapatkan">
                    <TextInput
                        id="points-earned"
                        v-model="form.points_earned"
                        type="number"
                        min="1"
                        placeholder="Masukkan Poin yang Didapatkan"
                        required
                        :error="form.errors.points_earned"
                        @update:modelValue="form.errors.points_earned = null"
                    />
                    <template #suffix>
                        <InfoTooltip
                            id="points-earned-tooltip"
                            text="Jumlah poin yang akan diberikan kepada pelanggan ketika aturan ini terpenuhi."
                        />
                    </template>
                </InputGroup>
            </div>

            <!-- Conversion Rate -->
            <InputGroup
                v-if="form.type === 'per_transaction'"
                id="conversion-rate"
                label="Rasio Nominal Belanja ke Poin"
            >
                <TextInput
                    id="conversion-rate"
                    v-model="form.conversion_rate"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="Masukkan Rasio Nominal Belanja ke Poin"
                    required
                    :error="form.errors.conversion_rate"
                    @update:modelValue="form.errors.conversion_rate = null"
                />
                <template #suffix>
                    <InfoTooltip
                        id="conversion-rate-tooltip"
                        text="Rasio yang menentukan berapa banyak poin yang didapatkan untuk setiap nominal tertentu yang dibelanjakan pelanggan. Misalnya, jika rasio adalah 10, maka pelanggan akan mendapatkan 1 poin untuk setiap Rp 10.000 yang mereka belanjakan."
                    />
                </template>
            </InputGroup>

            <!-- Minimum Spend (Conditional) -->
            <InputGroup
                v-if="form.type === 'per_amount_spent'"
                id="min-spend"
                label="Nominal Belanja Minimum"
            >
                <TextInput
                    id="min-spend"
                    v-model="form.min_spend"
                    type="number"
                    min="0"
                    placeholder="Masukkan Nominal Belanja Minimum"
                    required
                    :error="form.errors.min_spend"
                    @update:modelValue="form.errors.min_spend = null"
                />
                <template #suffix>
                    <InfoTooltip
                        id="min-spend-tooltip"
                        text="Nominal belanja minimum yang harus dicapai pelanggan agar aturan poin ini berlaku."
                    />
                </template>
            </InputGroup>

            <div class="flex items-center w-full gap-4">
                <!-- Valid From -->
                <InputGroup for="valid-from" label="Berlaku Mulai">
                    <DateInput
                        id="valid-from"
                        v-model="form.valid_from"
                        placeholder="Pilih Tanggal Mulai Berlaku"
                        :error="form.errors.valid_from"
                        @update:modelValue="form.errors.valid_from = null"
                    />
                    <template #suffix>
                        <InfoTooltip
                            id="valid-from-tooltip"
                            text="Tanggal mulai aturan poin ini berlaku. Jika tidak diisi, aturan akan berlaku segera setelah disimpan."
                        />
                    </template>
                </InputGroup>

                <!-- Valid Until -->
                <InputGroup for="valid-until" label="Berlaku Hingga">
                    <DateInput
                        id="valid-until"
                        v-model="form.valid_until"
                        placeholder="Pilih Tanggal Berakhir Berlaku"
                        :error="form.errors.valid_until"
                        @update:modelValue="form.errors.valid_until = null"
                    />
                    <template #suffix>
                        <InfoTooltip
                            id="valid-until-tooltip"
                            text="Tanggal berakhir aturan poin ini berlaku. Jika tidak diisi, aturan akan berlaku tanpa batas waktu."
                        />
                    </template>
                </InputGroup>
            </div>

            <div class="flex items-center gap-4 mt-4">
                <PrimaryButton type="submit"> Simpan </PrimaryButton>
                <SecondaryButton
                    v-if="!props.isDialog"
                    type="button"
                    @click="goBack()"
                >
                    Kembali
                </SecondaryButton>
                <SecondaryButton v-else type="button" @click="emit('close')">
                    Batalkan
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

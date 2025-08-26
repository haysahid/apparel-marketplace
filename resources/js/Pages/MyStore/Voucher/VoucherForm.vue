<script setup>
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import InputGroup from "@/Components/InputGroup.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";

const props = defineProps({
    voucher: {
        type: Object,
        default: null,
    },
});

const form = useForm(
    props.voucher ?? {
        name: null,
        code: null,
        description: null,
        type: "percentage",
        amount: null,
        min_amount: null,
        max_amount: null,
        start_date: null,
        end_date: null,
        usage_limit: null,
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

const startDateInput = ref(null);
const endDateInput = ref(null);

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
</script>

<template>
    <form @submit.prevent="submit" class="">
        <div class="flex flex-col items-start gap-4">
            <div class="flex flex-col w-full gap-y-4 gap-x-6 sm:flex-row">
                <div class="flex flex-col w-full max-w-3xl gap-4">
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
                            @update:modelValue="form.errors.name = null"
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
                            @update:modelValue="form.errors.code = null"
                        />
                    </InputGroup>

                    <!-- Description -->
                    <InputGroup id="description" label="Deskripsi Voucher">
                        <TextAreaInput
                            id="description"
                            v-model="form.description"
                            type="text"
                            placeholder="Masukkan Deskripsi"
                            autocomplete="description"
                            :error="form.errors.description"
                            @update:modelValue="form.errors.description = null"
                        />
                    </InputGroup>

                    <!-- Type -->
                    <InputGroup id="type" label="Tipe Voucher">
                        <DropdownSearchInput
                            id="type"
                            :modelValue="
                                form.type
                                    ? {
                                          label:
                                              form.type === 'percentage'
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
                        :label="`Jumlah Voucher (${
                            form.type === 'percentage' ? '%' : 'Rp'
                        })`"
                    >
                        <TextInput
                            id="amount"
                            v-model="form.amount"
                            type="number"
                            placeholder="Masukkan Jumlah Voucher"
                            required
                            :error="form.errors.amount"
                            @update:modelValue="form.errors.amount = null"
                        />
                    </InputGroup>
                </div>

                <div class="flex flex-col w-full max-w-3xl gap-4">
                    <!-- Start Date -->
                    <InputGroup id="start_date" label="Tanggal Mulai">
                        <TextInput
                            ref="startDateInput"
                            v-model="form.start_date"
                            type="date"
                            placeholder="Tanggal Mulai"
                            :error="form.errors.start_date"
                            @change="form.errors.start_date = null"
                        >
                            <template #suffix>
                                <div class="absolute right-1.5">
                                    <button
                                        type="button"
                                        class="p-2"
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
                    </InputGroup>

                    <!-- End Date -->
                    <InputGroup id="end_date" label="Tanggal Berakhir">
                        <TextInput
                            ref="endDateInput"
                            v-model="form.end_date"
                            type="date"
                            placeholder="Tanggal Berakhir"
                            :error="form.errors.end_date"
                            @change="form.errors.end_date = null"
                        >
                            <template #suffix>
                                <div class="absolute right-1.5">
                                    <button
                                        type="button"
                                        class="p-2"
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
                                </div>
                            </template>
                        </TextInput>
                    </InputGroup>

                    <!-- Usage Limit -->
                    <InputGroup id="usage_limit" label="Batas Penggunaan">
                        <TextInput
                            id="usage_limit"
                            v-model="form.usage_limit"
                            type="number"
                            placeholder="Masukkan Batas Penggunaan Voucher"
                            :error="form.errors.usage_limit"
                            @update:modelValue="form.errors.usage_limit = null"
                        />
                    </InputGroup>

                    <!-- Min. Amount -->
                    <InputGroup id="min_amount" label="Minimal Pembelian (Rp)">
                        <TextInput
                            id="min_amount"
                            v-model="form.min_amount"
                            type="number"
                            placeholder="Masukkan Minimal Pembelian"
                            :error="form.errors.min_amount"
                            @update:modelValue="form.errors.min_amount = null"
                        />
                    </InputGroup>

                    <!-- Max. Amount -->
                    <InputGroup id="max_amount" label="Maksimal Voucher (Rp)">
                        <TextInput
                            id="max_amount"
                            v-model="form.max_amount"
                            type="number"
                            placeholder="Masukkan Maksimal Voucher"
                            :error="form.errors.max_amount"
                            @update:modelValue="form.errors.max_amount = null"
                        />
                    </InputGroup>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-4">
                <PrimaryButton type="submit"> Simpan </PrimaryButton>
                <SecondaryButton
                    type="button"
                    @click="$inertia.visit(route('my-store.voucher'))"
                >
                    Kembali
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

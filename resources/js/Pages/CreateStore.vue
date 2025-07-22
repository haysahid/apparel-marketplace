<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import LandingLayout from "@/Layouts/LandingLayout.vue";
import useDebounce from "@/plugins/debounce";
import { onMounted, ref, watch } from "vue";
import axios from "axios";
import InputLabel from "@/Components/InputLabel.vue";
import Dropdown from "@/Components/Dropdown.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";

const originSearch = ref("");
const isOriginDropdownOpen = ref(false);
const origins = ref([] as DestinationEntity[]);

function getOrigins(search) {
    axios
        .get("/api/destinations", {
            params: {
                search: search,
            },
        })
        .then((response) => {
            origins.value = response.data.result;
        })
        .catch((error) => {});
}

const debouncedGetOrigins = useDebounce(getOrigins, 400);

watch(
    () => originSearch.value,
    (newValue) => {
        if (newValue) {
            debouncedGetOrigins(newValue);
        } else {
            origins.value = [];
        }
    }
);

const form = useForm({
    name: "",
    description: "",
    address: "",
    email: "",
    phone: "",
    rajaongkir_origin_id: null,
    rajaongkir_origin: null,
});

const submit = () => {
    form.transform((data) => ({
        name: data.name,
        description: data.description,
        address: data.address,
        email: data.email,
        phone: data.phone,
        rajaongkir_origin_id: data.rajaongkir_origin_id,
        rajaongkir_origin_label: data.rajaongkir_origin?.label || null,
        province_name: data.rajaongkir_origin?.province_name || null,
        city_name: data.rajaongkir_origin?.city_name || null,
        district_name: data.rajaongkir_origin?.district_name || null,
        subdistrict_name: data.rajaongkir_origin?.subdistrict_name || null,
        zip_code: data.rajaongkir_origin?.zip_code || null,
    })).post(route("store.store"), {
        onError: (errors) => {
            if (errors.error) {
                openErrorDialog(errors.error);
            }
        },
    });
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
</script>

<template>
    <LandingLayout title="Buat Toko">
        <AuthenticationCard class="!min-h-[100vh] !pb-[200px]">
            <template #logo>
                <div
                    class="flex flex-col items-center justify-center gap-2 mb-6 sm:flex-row"
                >
                    <h1 class="text-2xl font-bold text-gray-800 sm:block">
                        Buat Toko
                    </h1>
                </div>
            </template>

            <div
                v-if="status"
                class="mb-4 text-sm font-medium text-center text-green-600"
            >
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <!-- Name -->
                <div class="flex flex-wrap gap-2">
                    <InputLabel
                        for="name"
                        value="Nama Toko"
                        class="!text-gray-500"
                    />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        placeholder="Masukkan nama toko"
                        class="block w-full mt-1"
                        required
                        :autofocus="true"
                        autocomplete="name"
                        :error="form.errors.name"
                        @update:modelValue="form.errors.name = null"
                    />
                </div>

                <!-- Description -->
                <div class="flex flex-wrap gap-2 mt-4">
                    <InputLabel
                        for="description"
                        value="Deskripsi Toko"
                        class="!text-gray-500"
                    />
                    <TextInput
                        id="description"
                        v-model="form.description"
                        type="text"
                        placeholder="Masukkan deskripsi toko"
                        class="block w-full mt-1"
                        required
                        autocomplete="description"
                        :error="form.errors.description"
                        @update:modelValue="form.errors.description = null"
                    />
                </div>

                <!-- Phone -->
                <div class="flex flex-wrap gap-2 mt-4">
                    <InputLabel
                        for="phone"
                        value="Nomor Telepon"
                        class="!text-gray-500"
                    />
                    <TextInput
                        id="phone"
                        v-model="form.phone"
                        type="text"
                        placeholder="Masukkan nomor telepon"
                        class="block w-full mt-1"
                        required
                        autocomplete="phone"
                        :error="form.errors.phone"
                        @update:modelValue="form.errors.phone = null"
                    />
                </div>

                <!-- Origin -->
                <div class="flex flex-col gap-2 mt-4">
                    <InputLabel
                        for="rajaongkir_origin_id"
                        value="Alamat Toko"
                        class="!text-gray-500"
                    />
                    <Dropdown
                        v-if="origins"
                        id="rajaongkir_origin_id"
                        v-model="form.rajaongkir_origin_id"
                        :options="origins"
                        option-label="name"
                        option-value="id"
                        placeholder="Cari Alamat Toko"
                        align="left"
                        required
                        :error="form.errors.rajaongkir_origin_id"
                        @update:modelValue="
                            form.errors.rajaongkir_origin_id = null
                        "
                        @onOpen="isOriginDropdownOpen = true"
                        @onClose="isOriginDropdownOpen = false"
                    >
                        <template #trigger>
                            <TextAreaInput
                                :modelValue="
                                    form.rajaongkir_origin_id &&
                                    !isOriginDropdownOpen
                                        ? form.rajaongkir_origin?.label
                                        : originSearch
                                "
                                @update:modelValue="
                                    form.rajaongkir_origin_id &&
                                    !isOriginDropdownOpen
                                        ? null
                                        : (originSearch = $event)
                                "
                                class="w-full"
                                placeholder="Cari Alamat Toko"
                                :rows="1"
                                :preventNewLine="true"
                                :error="
                                    form.errors?.rajaongkir_origin_id
                                        ? form.errors
                                              ?.rajaongkir_origin_id[0] || null
                                        : null
                                "
                            >
                                <template #suffix>
                                    <button
                                        v-if="
                                            form.rajaongkir_origin_id &&
                                            !isOriginDropdownOpen
                                        "
                                        type="button"
                                        class="absolute p-[7px] text-gray-400 bg-white rounded-full top-1 right-1 hover:bg-gray-100 transition-all duration-300 ease-in-out"
                                        @click="
                                            form.rajaongkir_origin_id = null;
                                            form.rajaongkir_origin = null;
                                            originSearch = '';
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
                            </TextAreaInput>
                        </template>
                        <template #content>
                            <ul class="overflow-y-auto max-h-60">
                                <li
                                    v-for="origin in origins"
                                    :key="origin.id"
                                    @click="
                                        isOriginDropdownOpen = false;

                                        form.rajaongkir_origin_id = origin.id;
                                        form.rajaongkir_origin = origin;
                                        originSearch = '';
                                    "
                                    class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                                >
                                    {{ origin.label }}
                                </li>
                            </ul>
                        </template>
                    </Dropdown>
                </div>

                <!-- Address -->
                <div class="flex flex-col gap-2 mt-4">
                    <InputLabel
                        for="address"
                        value="Alamat Lengkap"
                        class="!text-gray-500"
                    />
                    <TextAreaInput
                        id="address"
                        v-model="form.address"
                        class="w-full"
                        placeholder="Masukkan alamat lengkap"
                        @update:modelValue="form.errors.address = null"
                        :error="
                            form.errors?.address
                                ? form.errors?.address[0] || null
                                : null
                        "
                    />
                </div>

                <div class="flex items-center justify-center mt-8">
                    <PrimaryButton
                        class="px-4 py-2 w-full max-w-[240px]"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Buat Toko
                    </PrimaryButton>
                </div>
            </form>

            <ErrorDialog
                :show="showErrorDialog"
                :title="errorMessage"
                @close="closeErrorDialog"
            />
        </AuthenticationCard>
    </LandingLayout>
</template>

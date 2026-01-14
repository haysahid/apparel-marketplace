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
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import InputGroup from "@/Components/InputGroup.vue";

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

            <form @submit.prevent="submit">
                <!-- Name -->
                <InputGroup for="name" label="Nama Toko">
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
                </InputGroup>

                <!-- Description -->
                <InputGroup
                    id="description"
                    label="Deskripsi Toko"
                    class="mt-4"
                >
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
                </InputGroup>

                <!-- Phone -->
                <InputGroup for="phone" label="Nomor Telepon" class="mt-4">
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
                </InputGroup>

                <!-- Origin -->
                <InputGroup
                    id="rajaongkir_origin_id"
                    label="Alamat Toko"
                    class="mt-4"
                >
                    <DropdownSearchInput
                        id="rajaongkir_origin_id"
                        :modelValue="
                            form.rajaongkir_origin_id
                                ? {
                                      label:
                                          form.rajaongkir_origin?.label ||
                                          form.rajaongkir_origin_id,
                                      value: form.rajaongkir_origin_id,
                                  }
                                : null
                        "
                        :options="
                            origins.map((origin) => ({
                                label: origin.label,
                                value: origin.id,
                            }))
                        "
                        placeholder="Cari Alamat Toko"
                        required
                        type="textarea"
                        :error="form.errors.rajaongkir_origin_id"
                        @update:modelValue="
                            (option) => {
                                form.rajaongkir_origin_id = option.value;
                                form.rajaongkir_origin = origins.find(
                                    (o) => o.id === option.value
                                );
                                form.errors.rajaongkir_origin_id = null;
                            }
                        "
                        @search="originSearch = $event"
                        @clear="
                            form.rajaongkir_origin_id = null;
                            form.rajaongkir_origin = null;
                        "
                    />
                </InputGroup>

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
                        required
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

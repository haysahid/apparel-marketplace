<script setup lang="ts">
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import DialogModal from "@/Components/DialogModal.vue";
import LinkItem from "@/Components/LinkItem.vue";
import IconTikTok from "@/Icons/IconTikTok.vue";
import IconInstagram from "@/Icons/IconInstagram.vue";
import IconFacebook from "@/Icons/IconFacebook.vue";
import SocialLinkForm from "./Store/SocialLinkForm.vue";
import Dropdown from "@/Components/Dropdown.vue";
import axios from "axios";
import useDebounce from "@/plugins/debounce";

const props = defineProps({
    store: {
        type: Object,
        required: null,
    },
});

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
            isOriginDropdownOpen.value = true;
        })
        .catch((error) => {
            isOriginDropdownOpen.value = false;
        });
}

const debouncedGetOrigins = useDebounce(getOrigins, 400);

watch(
    () => originSearch.value,
    (newValue) => {
        if (newValue) {
            debouncedGetOrigins(newValue);
        } else {
            origins.value = [];
            isOriginDropdownOpen.value = false;
        }
    }
);

const form = useForm({
    ...props.store,
    rajaongkir_origin_id: props.store.rajaongkir_origin_id || null,
    rajaongkir_origin: null,
    social_links: [
        ...props.store.social_links.map(function (link) {
            if (!link.icon) return link;
            return {
                ...link,
                icon: "/storage/" + link.icon,
            };
        }),
    ],
});

// Initialize origin
if (props.store.zip_code) {
    axios
        .get("/api/destinations", {
            params: {
                search: props.store.zip_code,
            },
        })
        .then((response) => {
            if (response.data.result.length > 0) {
                const origin = response.data.result[0];
                form.rajaongkir_origin_id = origin.id;
                form.rajaongkir_origin = origin;
            }
        })
        .catch((error) => {
            console.error("Error fetching origin:", error);
        });
}

const submit = () => {
    form.post(route("admin.store.update"), {
        onSuccess: () => {
            openSuccessDialog("Informasi Toko berhasil diperbarui.");
        },
        onError: (errors) => {
            openErrorDialog(errors.error);
        },
    });
};

const showSuccessDialog = ref(false);
const successMessage = ref(null);

const showErrorDialog = ref(false);
const errorMessage = ref(null);

const openSuccessDialog = (message) => {
    successMessage.value = message;
    showSuccessDialog.value = true;
};

const openErrorDialog = (message) => {
    errorMessage.value = message;
    showErrorDialog.value = true;
};
</script>

<template>
    <AdminLayout title="Informasi Toko" :showTitle="true">
        <template #icon>
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="26"
                height="27"
                viewBox="0 0 26 27"
                class="fill-primary"
            >
                <rect
                    opacity="0.01"
                    x="26"
                    y="26.6953"
                    width="26"
                    height="26"
                    transform="rotate(180 26 26.6953)"
                />
                <path
                    d="M3.51 8.59243L12.74 13.6083C12.9025 13.6949 13.0975 13.6949 13.26 13.6083L22.49 8.59243C22.6622 8.5074 22.7665 8.32734 22.7547 8.13567C22.7429 7.94399 22.6172 7.77814 22.4358 7.71493L13.2058 3.94493C13.074 3.89075 12.926 3.89075 12.7942 3.94493L3.56416 7.71493C3.38282 7.77814 3.25713 7.94399 3.24529 8.13567C3.23346 8.32734 3.33781 8.5074 3.51 8.59243Z"
                />
                <path
                    d="M22.4359 12.2434L20.4534 11.3984L13.26 15.3093C13.0975 15.3959 12.9025 15.3959 12.74 15.3093L5.54669 11.3984L3.56419 12.2434C3.39307 12.3333 3.28589 12.5106 3.28589 12.7039C3.28589 12.8971 3.39307 13.0744 3.56419 13.1643L12.7942 18.4726C12.9538 18.5702 13.1546 18.5702 13.3142 18.4726L22.5442 13.1643C22.7072 13.0558 22.7954 12.865 22.7725 12.6706C22.7497 12.4762 22.6195 12.3111 22.4359 12.2434Z"
                />
                <path
                    d="M22.4359 17.054L20.7459 16.3174L13.26 20.3907C13.0975 20.4774 12.9025 20.4774 12.74 20.3907L5.25419 16.3174L3.56419 17.054C3.38819 17.1453 3.27771 17.327 3.27771 17.5253C3.27771 17.7236 3.38819 17.9053 3.56419 17.9965L12.7942 23.4132C12.9567 23.4999 13.1517 23.4999 13.3142 23.4132L22.5442 17.9965C22.7121 17.8866 22.8036 17.6913 22.7807 17.4919C22.7578 17.2925 22.6243 17.1231 22.4359 17.054Z"
                />
            </svg>
        </template>

        <div class="md:px-11">
            <form @submit.prevent="submit" class="max-w-3xl">
                <div class="flex flex-col items-start gap-4">
                    <!-- Name -->
                    <div
                        class="flex flex-col w-full gap-y-1 gap-x-4 sm:items-center sm:flex-row"
                    >
                        <InputLabel
                            for="name"
                            value="Nama Toko"
                            class="w-[100px] sm:w-1/5 text-lg font-bold"
                        />
                        <span class="hidden text-sm sm:block">:</span>
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Masukkan Nama Toko"
                            class="block w-full mt-1"
                            required
                            :autofocus="true"
                            autocomplete="name"
                            :error="form.errors.username"
                            @update:modelValue="form.errors.username = null"
                        />
                    </div>

                    <!-- Phone -->
                    <div
                        class="flex flex-col w-full gap-y-1 gap-x-4 sm:items-center sm:flex-row"
                    >
                        <InputLabel
                            for="phone"
                            value="No. WhatsApp"
                            class="w-[100px] sm:w-1/5 text-lg font-bold"
                        />
                        <span class="hidden text-sm sm:block">:</span>
                        <TextInput
                            id="phone"
                            v-model="form.phone"
                            type="text"
                            placeholder="Masukkan No. WhatsApp"
                            class="block w-full mt-1"
                            required
                            autocomplete="phone"
                            :error="form.errors.phone"
                            @update:modelValue="form.errors.phone = null"
                        />
                    </div>

                    <!-- Email -->
                    <div
                        class="flex flex-col w-full gap-y-1 gap-x-4 sm:items-center sm:flex-row"
                    >
                        <InputLabel
                            for="email"
                            value="Email"
                            class="w-[100px] sm:w-1/5 text-lg font-bold"
                        />
                        <span class="hidden text-sm sm:block">:</span>
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="Masukkan Email"
                            class="block w-full mt-1"
                            required
                            autocomplete="email"
                            :error="form.errors.email"
                            @update:modelValue="form.errors.email = null"
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
                                                  ?.rajaongkir_origin_id[0] ||
                                              null
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
                                                form.rajaongkir_origin_id =
                                                    null;
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

                                            form.rajaongkir_origin_id =
                                                origin.id;
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
                    <div
                        class="flex flex-col w-full gap-y-1 gap-x-4 sm:items-center sm:flex-row"
                    >
                        <InputLabel
                            for="address"
                            value="Alamat Lengkap"
                            class="w-[100px] sm:w-1/5 text-lg font-bold"
                        />
                        <span class="hidden text-sm sm:block">:</span>
                        <TextAreaInput
                            id="address"
                            v-model="form.address"
                            type="text"
                            placeholder="Masukkan Alamat Lengkap Toko"
                            class="block w-full mt-1"
                            required
                            autocomplete="address"
                            :rows="1"
                            :preventNewLine="true"
                            :error="form.errors.address"
                            @update:modelValue="form.errors.address = null"
                        />
                    </div>

                    <!-- Advantages -->
                    <div class="w-full">
                        <InputLabel
                            value="Keunggulan Toko"
                            class="mb-2.5 sm:mb-4"
                        />
                        <div
                            class="grid grid-cols-1 p-4 gap-x-6 gap-y-4 rounded-2xl sm:grid-cols-2 border-dashed-default"
                        >
                            <div
                                v-for="(advantage, index) in form.advantages"
                                :key="index"
                                class="flex flex-col items-start gap-2"
                            >
                                <InputLabel
                                    :for="'advantage_name_' + index"
                                    :value="'Keunggulan ' + (index + 1)"
                                    class="text-lg font-bold"
                                />
                                <div class="flex flex-col w-full gap-2">
                                    <TextInput
                                        :id="'advantage_name_' + index"
                                        v-model="advantage.name"
                                        type="text"
                                        placeholder="Masukkan Nama Keunggulan"
                                        class="block w-full mt-1"
                                        required
                                        autocomplete="advantage_name"
                                        :error="
                                            form.errors.advantages?.[index]
                                                ?.name
                                        "
                                    />
                                    <TextAreaInput
                                        :id="'advantage_description_' + index"
                                        v-model="advantage.description"
                                        type="text"
                                        placeholder="Masukkan Deskripsi Keunggulan"
                                        height="sm:max-h-[110px]"
                                        class="block w-full mt-1"
                                        required
                                        autocomplete="advantage_description"
                                        :error="
                                            form.errors.advantages?.[index]
                                                ?.description
                                        "
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div
                        class="flex flex-col items-start w-full gap-1 sm:gap-1.5"
                    >
                        <InputLabel
                            for="description"
                            value="Deskripsi Toko"
                            class="text-lg font-bold"
                        />
                        <TextAreaInput
                            id="description"
                            v-model="form.description"
                            type="text"
                            placeholder="Masukkan Deskripsi Toko"
                            class="block w-full mt-1"
                            required
                            autocomplete="description"
                            :error="form.errors.description"
                            @update:modelValue="form.errors.description = null"
                        />
                    </div>

                    <!-- Social Links -->
                    <div class="flex flex-col items-start w-full gap-2 mt-4">
                        <h2 class="text-lg font-semibold">Tautan Sosial</h2>
                        <div
                            v-for="(link, index) in form.social_links"
                            :key="index"
                            class="flex flex-col items-start w-full gap-2"
                        >
                            <LinkItem
                                :name="link.name"
                                :url="link.url"
                                :index="index"
                                :showDeleteButton="false"
                                @click="link.showEditForm = true"
                            >
                                <template v-if="link.name == 'Instagram'" #icon>
                                    <IconInstagram />
                                </template>
                                <template
                                    v-else-if="link.name == 'Facebook'"
                                    #icon
                                >
                                    <IconFacebook />
                                </template>
                                <template
                                    v-else-if="link.name == 'TikTok'"
                                    #icon
                                >
                                    <IconTikTok />
                                </template>
                            </LinkItem>
                            <DialogModal
                                :show="link.showEditForm"
                                title="Tambah Tautan Produk"
                                maxWidth="sm"
                                @close="link.showEditForm = false"
                            >
                                <template #content>
                                    <SocialLinkForm
                                        :link="link"
                                        @submit="
                                            form.social_links[index] = $event
                                        "
                                        @close="link.showEditForm = false"
                                    />
                                </template>
                            </DialogModal>
                        </div>
                    </div>
                    <PrimaryButton type="submit" class="mt-4">
                        Simpan Data
                    </PrimaryButton>
                </div>

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
            </form>
        </div>
    </AdminLayout>
</template>

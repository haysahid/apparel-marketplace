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
import SocialLinkForm from "@/Pages/MyStore/Store/SocialLinkForm.vue";
import Dropdown from "@/Components/Dropdown.vue";
import axios from "axios";
import useDebounce from "@/plugins/debounce";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import InputGroup from "@/Components/InputGroup.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

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
    form.transform((data) => ({
        ...data,
        rajaongkir_origin_id: data.rajaongkir_origin_id || null,
        rajaongkir_origin_label: data.rajaongkir_origin?.label || null,
        province_name: data.rajaongkir_origin?.province_name || null,
        city_name: data.rajaongkir_origin?.city_name || null,
        district_name: data.rajaongkir_origin?.district_name || null,
        subdistrict_name: data.rajaongkir_origin?.subdistrict_name || null,
        zip_code: data.rajaongkir_origin?.zip_code || null,
    })).post(route("my-store.store.update"), {
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
    <MyStoreLayout title="Informasi Toko" :showTitle="true">
        <DefaultCard>
            <form @submit.prevent="submit">
                <div
                    class="flex flex-col items-start gap-y-4 gap-x-6 lg:flex-row"
                >
                    <div class="flex flex-col w-full gap-4">
                        <!-- Name -->
                        <InputGroup id="name" label="Nama Toko">
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Masukkan Nama Toko"
                                required
                                autocomplete="name"
                                :error="form.errors.username"
                                @update:modelValue="form.errors.username = null"
                            />
                        </InputGroup>

                        <!-- Phone -->
                        <InputGroup id="phone" label="No. WhatsApp">
                            <TextInput
                                id="phone"
                                v-model="form.phone"
                                type="text"
                                placeholder="Masukkan No. WhatsApp"
                                required
                                autocomplete="phone"
                                :error="form.errors.phone"
                                @update:modelValue="form.errors.phone = null"
                            />
                        </InputGroup>

                        <!-- Email -->
                        <InputGroup id="email" label="Email">
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="Masukkan Email"
                                required
                                autocomplete="email"
                                :error="form.errors.email"
                                @update:modelValue="form.errors.email = null"
                            />
                        </InputGroup>

                        <!-- Origin -->
                        <InputGroup
                            id="rajaongkir_origin_id"
                            label="Alamat Toko"
                        >
                            <DropdownSearchInput
                                id="rajaongkir_origin_id"
                                :modelValue="
                                    form.rajaongkir_origin_id
                                        ? {
                                              label:
                                                  form.rajaongkir_origin
                                                      ?.label ||
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
                                        form.rajaongkir_origin_id =
                                            option.value;
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
                        <InputGroup id="address" label="Alamat Lengkap">
                            <TextAreaInput
                                id="address"
                                v-model="form.address"
                                type="text"
                                placeholder="Masukkan Alamat Lengkap Toko"
                                required
                                autocomplete="address"
                                :rows="1"
                                :preventNewLine="true"
                                :error="form.errors.address"
                                @update:modelValue="form.errors.address = null"
                            />
                        </InputGroup>

                        <!-- Description -->
                        <InputGroup id="description" label="Deskripsi Toko">
                            <TextAreaInput
                                id="description"
                                v-model="form.description"
                                type="text"
                                placeholder="Masukkan Deskripsi Toko"
                                class="block w-full mt-1"
                                required
                                autocomplete="description"
                                :error="form.errors.description"
                                @update:modelValue="
                                    form.errors.description = null
                                "
                            />
                        </InputGroup>

                        <!-- Advantages -->
                        <InputGroup id="advantages" label="Keunggulan Toko">
                            <div
                                class="grid grid-cols-1 p-4 gap-x-6 gap-y-4 rounded-2xl sm:grid-cols-2 border-dashed-default"
                            >
                                <div
                                    v-for="(
                                        advantage, index
                                    ) in form.advantages"
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
                                            autocomplete="advantage_name"
                                            :error="
                                                form.errors.advantages?.[index]
                                                    ?.name
                                            "
                                        />
                                        <TextAreaInput
                                            :id="
                                                'advantage_description_' + index
                                            "
                                            v-model="advantage.description"
                                            type="text"
                                            placeholder="Masukkan Deskripsi Keunggulan"
                                            height="sm:max-h-[110px]"
                                            class="block w-full mt-1"
                                            autocomplete="advantage_description"
                                            :error="
                                                form.errors.advantages?.[index]
                                                    ?.description
                                            "
                                        />
                                    </div>
                                </div>
                            </div>
                        </InputGroup>
                    </div>

                    <div class="w-full max-w-xs">
                        <!-- Social Links -->
                        <div class="flex flex-col items-start w-full gap-2">
                            <InputGroup label="Tautan Sosial">
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
                                        <template
                                            v-if="link.name == 'Instagram'"
                                            #icon
                                        >
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
                                                    form.social_links[index] =
                                                        $event
                                                "
                                                @close="
                                                    link.showEditForm = false
                                                "
                                            />
                                        </template>
                                    </DialogModal></div
                            ></InputGroup>
                        </div>
                    </div>
                </div>

                <PrimaryButton type="submit" class="mt-6">
                    Simpan Data
                </PrimaryButton>

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
        </DefaultCard>
    </MyStoreLayout>
</template>

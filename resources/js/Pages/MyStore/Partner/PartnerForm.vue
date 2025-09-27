<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ImageInput from "@/Components/ImageInput.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import InputGroup from "@/Components/InputGroup.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    partner: {
        type: Object,
        default: null,
    },
    isDialog: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["onSubmitted", "close"]);

const form = useForm(
    props.partner || {
        user_id: null,
        name: null,
        slug: null,
        description: null,
        logo: null,
        contact_name: null,
        contact_email: null,
        contact_phone: null,
        address: null,
        website: null,
    }
);

const submit = () => {
    if (props.partner?.id) {
        form.transform((data) => {
            const formData = new FormData();
            Object.keys(data).forEach((key) => {
                if (key === "logo" && !(data[key] instanceof File)) {
                    return;
                }

                if (data[key] === null || data[key] === undefined) {
                    return;
                }

                formData.append(key, data[key]);
            });
            return formData;
        }).post(
            route("my-store.partner.update", {
                partner: props.partner,
            }),
            {
                onError: (errors) => {
                    openErrorDialog(errors.error);
                },
            }
        );
    } else {
        form.transform((data) => {
            return {
                ...data,
                is_dialog: props.isDialog ? 1 : 0,
            };
        }).post(route("my-store.partner.store"), {
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
</script>

<template>
    <form @submit.prevent="submit">
        <div class="flex flex-col items-start gap-4">
            <div class="flex flex-col w-full gap-y-4 gap-x-6 sm:flex-row">
                <div class="flex flex-col w-full max-w-3xl gap-4">
                    <!-- Name -->
                    <InputGroup id="name" label="Nama Mitra">
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Masukkan Nama Mitra"
                            required
                            :autofocus="true"
                            :error="form.errors.name"
                            @update:modelValue="form.errors.name = null"
                        />
                    </InputGroup>

                    <!-- Description -->
                    <InputGroup id="description" label="Deskripsi Mitra">
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

                    <!-- Logo -->
                    <InputGroup id="logo" label="Logo Mitra">
                        <ImageInput
                            id="logo"
                            v-model="form.logo"
                            type="file"
                            accept="image/*"
                            placeholder="Upload Logo Mitra"
                            width="max-w-[180px]"
                            height="h-[120px]"
                            :error="form.errors.logo"
                            @update:modelValue="form.errors.logo = null"
                        />
                    </InputGroup>

                    <!-- Address -->
                    <InputGroup id="address" label="Alamat Mitra">
                        <TextAreaInput
                            id="address"
                            v-model="form.address"
                            type="text"
                            placeholder="Masukkan Alamat Mitra"
                            autocomplete="address"
                            :error="form.errors.address"
                            @update:modelValue="form.errors.address = null"
                        />
                    </InputGroup>

                    <!-- Website -->
                    <InputGroup id="website" label="Website Mitra (Opsional)">
                        <TextInput
                            id="website"
                            v-model="form.website"
                            type="url"
                            placeholder="Masukkan Website Mitra"
                            autocomplete="website"
                            :error="form.errors.website"
                            @update:modelValue="form.errors.website = null"
                        />
                    </InputGroup>
                </div>

                <div class="flex flex-col w-full max-w-3xl gap-4">
                    <!-- Contact Name -->
                    <InputGroup id="contact_name" label="Nama Kontak">
                        <TextInput
                            id="contact_name"
                            v-model="form.contact_name"
                            type="text"
                            placeholder="Masukkan Nama Kontak"
                            autocomplete="contact_name"
                            :error="form.errors.contact_name"
                            @update:modelValue="form.errors.contact_name = null"
                        />
                    </InputGroup>

                    <!-- Contact Email -->
                    <InputGroup id="contact_email" label="Email Kontak">
                        <TextInput
                            id="contact_email"
                            v-model="form.contact_email"
                            type="email"
                            placeholder="Masukkan Email Kontak"
                            autocomplete="contact_email"
                            :error="form.errors.contact_email"
                            @update:modelValue="
                                form.errors.contact_email = null
                            "
                        />
                    </InputGroup>

                    <!-- Contact Phone -->
                    <InputGroup id="contact_phone" label="No. Telepon Kontak">
                        <TextInput
                            id="contact_phone"
                            v-model="form.contact_phone"
                            type="text"
                            placeholder="Masukkan No. Telepon Kontak"
                            autocomplete="contact_phone"
                            :error="form.errors.contact_phone"
                            @update:modelValue="
                                form.errors.contact_phone = null
                            "
                        />
                    </InputGroup>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-4">
                <PrimaryButton type="submit"> Simpan </PrimaryButton>
                <SecondaryButton
                    v-if="!isDialog"
                    type="button"
                    @click="$inertia.visit(route('my-store.partner'))"
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

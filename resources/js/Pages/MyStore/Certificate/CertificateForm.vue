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
    certificate: {
        type: Object,
        default: () => ({
            name: null,
            description: null,
            image: null,
        }),
    },
});

const form = useForm(
    props.certificate
        ? {
              ...props.certificate,
              image: props.certificate.image
                  ? "/storage/" + props.certificate.image
                  : null,
          }
        : {
              name: null,
              description: null,
              image: null,
          }
);

const submit = () => {
    if (props.certificate.id) {
        form.transform((data) => {
            const formData = new FormData();
            Object.keys(data).forEach((key) => {
                if (key === "image" && !(data[key] instanceof File)) {
                    return;
                }

                if (data[key] === null || data[key] === undefined) {
                    return;
                }

                formData.append(key, data[key]);
            });
            return formData;
        }).post(
            route("my-store.certificate.update", {
                storeCertificate: props.certificate,
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
        return;
    }

    form.post(route("my-store.certificate.store"), {
        onFinish: () => {
            form.reset();
        },
    });
};

const showErrorDialog = ref(false);
const errorMessage = ref(null);

const openErrorDialog = (message) => {
    errorMessage.value = message;
    showErrorDialog.value = true;
};
</script>

<template>
    <form @submit.prevent="submit" class="max-w-3xl">
        <div class="flex flex-col items-start gap-4">
            <!-- Name -->
            <InputGroup id="name" label="Nama Sertifikat">
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="Masukkan Nama Sertifikat"
                    class="block w-full mt-1"
                    required
                    :autofocus="true"
                    :error="form.errors.name"
                    @update:modelValue="form.errors.name = null"
                />
            </InputGroup>

            <!-- Image -->
            <InputGroup id="image" label="Gambar Sertifikat">
                <ImageInput
                    id="image"
                    v-model="form.image"
                    type="file"
                    accept="image/*"
                    placeholder="Upload Sertifikat"
                    class="block w-full mt-1 h-"
                    width="max-w-[180px]"
                    height="h-[120px]"
                    required
                    :error="form.errors.image"
                    @update:modelValue="form.errors.image = null"
                />
            </InputGroup>

            <!-- Description -->
            <InputGroup id="description" label="Deskripsi Sertifikat">
                <TextAreaInput
                    id="description"
                    v-model="form.description"
                    type="text"
                    placeholder="Masukkan Deskripsi"
                    class="block w-full mt-1"
                    required
                    autocomplete="description"
                    :error="form.errors.description"
                    @update:modelValue="form.errors.description = null"
                />
            </InputGroup>

            <div class="flex items-center gap-4">
                <PrimaryButton type="submit" class="mt-4">
                    Simpan
                </PrimaryButton>
                <SecondaryButton
                    type="button"
                    class="mt-4"
                    @click="$inertia.visit(route('my-store.certificate'))"
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

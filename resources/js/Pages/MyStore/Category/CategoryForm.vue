<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ImageInput from "@/Components/ImageInput.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import InputGroup from "@/Components/InputGroup.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    category: {
        type: Object,
        default: null,
    },
});

const form = useForm(
    props.category
        ? {
              ...props.category,
              image: props.category.image
                  ? "/storage/" + props.category.image
                  : null,
          }
        : {
              name: null,
              image: null,
          }
);

const submit = () => {
    if (props.category?.id) {
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
            route("my-store.category.update", {
                category: props.category,
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
        form.post(route("my-store.category.store"), {
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
    <form @submit.prevent="submit" class="max-w-3xl">
        <div class="flex flex-col items-start gap-4">
            <!-- Name -->
            <InputGroup id="name" label="Nama Kategori">
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="Masukkan Nama Kategori"
                    class="block w-full mt-1"
                    required
                    :autofocus="true"
                    :error="form.errors.name"
                    @update:modelValue="form.errors.name = null"
                />
            </InputGroup>

            <!-- Image -->
            <InputGroup id="image" label="Gambar Kategori">
                <ImageInput
                    id="image"
                    v-model="form.image"
                    type="file"
                    accept="image/*"
                    placeholder="Upload Gambar"
                    class="block w-full mt-1 h-"
                    width="max-w-[180px]"
                    height="h-[120px]"
                    required
                    :error="form.errors.image"
                    @update:modelValue="form.errors.image = null"
                />
            </InputGroup>

            <div class="flex items-center gap-4 mt-4">
                <PrimaryButton type="submit"> Simpan </PrimaryButton>
                <SecondaryButton
                    type="button"
                    @click="$inertia.visit(route('my-store.category'))"
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

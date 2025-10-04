<script setup lang="ts">
import { nextTick, onMounted, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ImageInput from "@/Components/ImageInput.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import InputGroup from "@/Components/InputGroup.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    brand: {
        type: Object,
        default: () => ({
            name: null,
            description: null,
            logo: null,
        }),
    },
    isDialog: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["onSubmitted", "close"]);

const form = useForm(
    props.brand || {
        name: null,
        description: null,
        logo: null,
    }
);

const submit = () => {
    if (props.brand?.id) {
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
            route("my-store.brand.update", {
                brand: props.brand,
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
        }).post(route("my-store.brand.store"), {
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
        const input = document.getElementById("brand-name") as HTMLInputElement;
        input?.focus();
    });
});
</script>

<template>
    <form @submit.prevent="submit" class="max-w-3xl">
        <div class="flex flex-col items-start gap-4">
            <!-- Name -->
            <InputGroup id="brand-name" label="Nama Brand">
                <TextInput
                    id="brand-name"
                    v-model="form.name"
                    type="text"
                    placeholder="Masukkan Nama Brand"
                    class="block w-full mt-1"
                    required
                    :autofocus="true"
                    :error="form.errors.name"
                    @update:modelValue="form.errors.name = null"
                />
            </InputGroup>

            <!-- Logo -->
            <InputGroup id="logo" label="Logo Brand">
                <ImageInput
                    id="logo"
                    v-model="form.logo"
                    type="file"
                    accept="image/*"
                    placeholder="Upload Logo Brand"
                    class="block w-full mt-1"
                    width="max-w-[180px]"
                    height="h-[120px]"
                    required
                    :error="form.errors.logo"
                    @update:modelValue="form.errors.logo = null"
                />
            </InputGroup>

            <!-- Description -->
            <InputGroup id="description" label="Deskripsi Brand">
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

            <div class="flex items-center gap-4 mt-4">
                <PrimaryButton type="submit"> Simpan </PrimaryButton>
                <SecondaryButton
                    v-if="!isDialog"
                    type="button"
                    @click="$inertia.visit(route('my-store.brand'))"
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

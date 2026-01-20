<script setup lang="ts">
import { nextTick, onMounted, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ImageInput from "@/Components/ImageInput.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import InputGroup from "@/Components/InputGroup.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { goBack } from "@/plugins/helpers";

const props = defineProps({
    category: {
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
    props.category || {
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
        form.transform((data) => {
            return {
                ...data,
                is_dialog: props.isDialog ? 1 : 0,
            };
        }).post(route("my-store.category.store"), {
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
            "category-name"
        ) as HTMLInputElement;
        input?.focus();
    });
});
</script>

<template>
    <form @submit.prevent="submit" class="max-w-3xl">
        <div class="flex flex-col items-start gap-4">
            <!-- Name -->
            <InputGroup for="category-name" label="Nama Kategori" required>
                <TextInput
                    id="category-name"
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
            <InputGroup for="image" label="Gambar Kategori">
                <ImageInput
                    id="image"
                    v-model="form.image"
                    type="file"
                    accept="image/*"
                    placeholder="Upload Gambar"
                    class="block w-full mt-1 h-"
                    width="max-w-[180px]"
                    height="h-[120px]"
                    :error="form.errors.image"
                    @update:modelValue="form.errors.image = null"
                />
            </InputGroup>

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

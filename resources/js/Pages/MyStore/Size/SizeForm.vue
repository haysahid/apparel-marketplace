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
    size: {
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
    props.size
        ? props.size
        : {
              name: null,
          }
);

const submit = () => {
    if (props.size?.id) {
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
            route("my-store.size.update", {
                size: props.size,
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
        }).post(route("my-store.size.store"), {
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
    <form @submit.prevent="submit" class="max-w-3xl">
        <div class="flex flex-col items-start gap-4">
            <!-- Name -->
            <InputGroup id="name" label="Nama Ukuran">
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="Masukkan Nama Ukuran"
                    class="block w-full mt-1"
                    required
                    :autofocus="true"
                    :error="form.errors.name"
                    @update:modelValue="form.errors.name = null"
                />
            </InputGroup>

            <div class="flex items-center gap-4 mt-4">
                <PrimaryButton type="submit"> Simpan </PrimaryButton>
                <SecondaryButton
                    v-if="!props.isDialog"
                    type="button"
                    @click="$inertia.visit(route('my-store.size'))"
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

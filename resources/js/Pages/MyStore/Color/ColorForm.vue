<script setup lang="ts">
import { nextTick, onMounted, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ImageInput from "@/Components/ImageInput.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import InputGroup from "@/Components/InputGroup.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { ColorPicker } from "vue3-colorpicker";
import "vue3-colorpicker/style.css";

const props = defineProps({
    color: {
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
    props.color
        ? props.color
        : {
              name: null,
              hex_code: "#000000",
          }
);

const submit = () => {
    if (props.color?.id) {
        form.transform((data) => {
            return {
                ...data,
                hex_code: data.hex_code?.toUpperCase(),
            };
        }).post(
            route("my-store.color.update", {
                color: props.color,
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
                hex_code: data.hex_code?.toUpperCase(),
                is_dialog: props.isDialog ? 1 : 0,
            };
        }).post(route("my-store.color.store"), {
            preserveScroll: props.isDialog,
            preserveState: props.isDialog,
            onError: (errors) => {
                console.log(errors);
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

const colorPicker = ref(null);

onMounted(() => {
    nextTick(() => {
        const input = document.getElementById("color-name") as HTMLInputElement;
        input?.focus();
    });
});
</script>

<template>
    <form @submit.prevent="submit" class="max-w-3xl">
        <div class="flex flex-col items-start gap-4">
            <!-- Name -->
            <InputGroup id="color-name" label="Nama Warna">
                <TextInput
                    id="color-name"
                    v-model="form.name"
                    type="text"
                    placeholder="Masukkan Nama Warna"
                    class="block w-full mt-1"
                    required
                    :autofocus="true"
                    :error="form.errors.name"
                    @update:modelValue="form.errors.name = null"
                />
            </InputGroup>

            <!-- Color -->
            <InputGroup id="hex_code" label="Pilih Warna">
                <button
                    type="button"
                    class="flex items-center p-1.5 border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-1 focus:border-primary-light focus:ring-primary-light w-fit"
                    @click="colorPicker.showPicker = true"
                >
                    <ColorPicker
                        ref="colorPicker"
                        id="hex_code"
                        v-model:pureColor="form.hex_code"
                        format="hex"
                        pickerType="chrome"
                        :disableAlpha="true"
                    />
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        class="w-6 h-6 fill-gray-600"
                    >
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M20.4769 3.51094C20.1983 3.23231 19.8675 3.01129 19.5035 2.86049C19.1395 2.70969 18.7494 2.63208 18.3554 2.63208C17.9614 2.63208 17.5712 2.70969 17.2072 2.86049C16.8432 3.01129 16.5124 3.23231 16.2339 3.51094L14.7009 5.04394C14.1423 4.77547 13.5142 4.68718 12.9033 4.79126C12.2924 4.89534 11.729 5.18667 11.2909 5.62494L10.5779 6.33894C10.3921 6.52467 10.2447 6.74518 10.1441 6.98789C10.0436 7.23059 9.99179 7.49073 9.99179 7.75344C9.99179 8.01615 10.0436 8.27629 10.1441 8.51899C10.2447 8.7617 10.3921 8.98221 10.5779 9.16794L4.09187 15.6529C3.81333 15.9317 3.59243 16.2625 3.44178 16.6266C3.29114 16.9907 3.21369 17.3809 3.21387 17.7749V19.5749C3.21387 19.8932 3.3403 20.1984 3.56534 20.4235C3.79038 20.6485 4.09561 20.7749 4.41387 20.7749H6.21387C7.00925 20.7742 7.7718 20.4577 8.33387 19.8949L14.8199 13.4109C15.0056 13.5967 15.2261 13.7441 15.4688 13.8447C15.7115 13.9453 15.9717 13.997 16.2344 13.997C16.4971 13.997 16.7572 13.9453 16.9999 13.8447C17.2426 13.7441 17.4631 13.5967 17.6489 13.4109L18.3629 12.6959C18.8011 12.2578 19.0925 11.6944 19.1965 11.0835C19.3006 10.4726 19.2123 9.84446 18.9439 9.28594L20.4769 7.75394C20.7555 7.47536 20.9765 7.14462 21.1273 6.78061C21.2781 6.4166 21.3557 6.02645 21.3557 5.63244C21.3557 5.23843 21.2781 4.84828 21.1273 4.48427C20.9765 4.12026 20.7555 3.78952 20.4769 3.51094ZM5.50687 17.0679L11.9919 10.5819L13.4059 11.9959L6.92087 18.4819C6.73337 18.6695 6.47906 18.7749 6.21387 18.7749H5.21387V17.7749C5.21392 17.5097 5.31932 17.2554 5.50687 17.0679Z"
                        />
                    </svg>
                </button>
            </InputGroup>

            <div class="flex items-center gap-4 mt-4">
                <PrimaryButton type="submit"> Simpan </PrimaryButton>
                <SecondaryButton
                    v-if="!props.isDialog"
                    type="button"
                    @click="$inertia.visit(route('my-store.color'))"
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

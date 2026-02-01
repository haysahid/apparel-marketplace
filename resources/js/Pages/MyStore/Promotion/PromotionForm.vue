<script setup lang="ts">
import { nextTick, onMounted, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ImageInput from "@/Components/ImageInput.vue";
import InputGroup from "@/Components/InputGroup.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { useDialogStore } from "@/stores/dialog-store";
import { goBack } from "@/plugins/helpers";
import DateInput from "@/Components/DateInput.vue";

const props = defineProps({
    promotion: {
        type: Object,
        default: () => ({
            name: null,
            description: null,
            image: null,
            redirection_url: null,
            start_date: null,
            end_date: null,
        }),
    },
});

const form = useForm(
    props.promotion || {
        name: null,
        description: null,
        image: null,
        redirection_url: null,
        start_date: null,
        end_date: null,
    },
);

const submit = () => {
    if (props.promotion?.id) {
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
            route("my-store.promotion.update", {
                promotion: props.promotion,
            }),
            {
                onError: (errors) => {
                    useDialogStore().openErrorDialog(errors.error);
                },
            },
        );
    } else {
        form.transform((data) => {
            return {
                ...data,
                image: data.image instanceof File ? data.image : null,
            };
        }).post(route("my-store.promotion.store"), {
            onError: (errors) => {
                useDialogStore().openErrorDialog(errors.error);
            },
            onSuccess: () => {
                form.reset();
            },
        });
    }
};

watch(
    () => form.redirection_url,
    (newValue) => {
        if (
            newValue &&
            !/^(https?:\/\/)?((([a-zA-Z\d-]+\.)+[a-zA-Z]{2,}|(\d{1,3}\.){3}\d{1,3}))(:\d{1,5})?(\/[^\s]*)?$/i.test(
                newValue.trim(),
            )
        ) {
            form.errors.redirection_url = "URL tidak valid.";
        } else {
            form.errors.redirection_url = null;
        }
    },
);

onMounted(() => {
    nextTick(() => {
        const input = document.getElementById(
            "promotion-name",
        ) as HTMLInputElement;
        input?.focus();
    });
});
</script>

<template>
    <form @submit.prevent="submit">
        <div class="flex flex-col items-start w-full gap-4">
            <div class="flex flex-col w-full gap-4">
                <div class="flex flex-col w-full gap-4">
                    <!-- Name -->
                    <InputGroup
                        for="promotion-name"
                        label="Nama/Judul Promosi"
                        required
                    >
                        <TextInput
                            id="promotion-name"
                            v-model="form.name"
                            type="text"
                            placeholder="Masukkan Nama/Judul Promosi"
                            required
                            :autofocus="true"
                            :error="form.errors.name"
                            @update:modelValue="form.errors.name = null"
                        />
                    </InputGroup>

                    <!-- Description -->
                    <InputGroup for="description" label="Deskripsi">
                        <TextAreaInput
                            id="description"
                            v-model="form.description"
                            type="text"
                            placeholder="Masukkan Deskripsi"
                            autocomplete="off"
                            :error="form.errors.description"
                            @update:modelValue="form.errors.description = null"
                        />
                    </InputGroup>

                    <!-- Image -->
                    <InputGroup for="image" label="Gambar " required>
                        <ImageInput
                            id="image"
                            v-model="form.image"
                            type="file"
                            accept="image/*"
                            placeholder="Upload Gambar"
                            width="max-w-[180px]"
                            height="h-[120px]"
                            required
                            :error="form.errors.image"
                            @update:modelValue="form.errors.image = null"
                        />
                    </InputGroup>
                </div>

                <div class="flex flex-col w-full gap-4">
                    <!-- Redirection URL -->
                    <InputGroup for="redirection_url" label="URL Tujuan">
                        <TextInput
                            id="redirection_url"
                            v-model="form.redirection_url"
                            type="url"
                            placeholder="Masukkan URL Tujuan"
                            autocomplete="off"
                            :error="form.errors.redirection_url"
                            @update:modelValue="
                                form.errors.redirection_url = null
                            "
                        />
                    </InputGroup>

                    <div class="flex w-full gap-4">
                        <!-- Start Date -->
                        <InputGroup for="start_date" label="Berlaku Dari">
                            <DateInput
                                id="start_date"
                                v-model="form.start_date"
                                type="datetime-local"
                                placeholder="Pilih Tanggal Mulai"
                                :error="form.errors.start_date"
                                @update:modelValue="
                                    form.errors.start_date = null
                                "
                            />
                        </InputGroup>

                        <!-- End Date -->
                        <InputGroup for="end_date" label="Berlaku Hingga">
                            <DateInput
                                id="end_date"
                                v-model="form.end_date"
                                type="datetime-local"
                                placeholder="Pilih Tanggal Berakhir"
                                :error="form.errors.end_date"
                                @update:modelValue="form.errors.end_date = null"
                            />
                        </InputGroup>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-4">
                <PrimaryButton type="submit"> Simpan </PrimaryButton>
                <SecondaryButton type="button" @click="goBack()">
                    Kembali
                </SecondaryButton>
            </div>
        </div>
    </form>
</template>

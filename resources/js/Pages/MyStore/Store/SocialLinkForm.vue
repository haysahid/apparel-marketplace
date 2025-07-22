<script setup lang="ts">
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm, usePage } from "@inertiajs/vue3";

interface SocialLink {
    id?: number | string;
    name?: string;
    url?: string;
}

const props = defineProps({
    link: {
        type: Object as () => SocialLink,
        default: null,
    },
});

const emit = defineEmits(["submit", "close"]);

const page = usePage();

const form = useForm(
    props.link
        ? {
              id: props.link?.id || null,
              name: props.link?.name || null,
              url: props.link?.url || null,
          }
        : {
              id: null,
              name: null,
              url: null,
          }
);

function validate() {
    if (form.url == null || form.url.trim() === "") {
        form.errors.url = "URL tidak boleh kosong.";
    } else if (
        !/^(https?:\/\/)?([\w-]+\.)+[\w-]{2,}(\/[^\s]*)?$/i.test(
            form.url.trim()
        )
    ) {
        form.errors.url = "URL tidak valid.";
    } else {
        form.errors.url = null;
    }
}

function submit() {
    validate();
    console.log(form.errors);
    if (form.errors.url) return;
    console.log("Submitting form:", form.data());
    emit("submit", form.data());
    emit("close");
}
</script>

<template>
    <form @submit.prevent="submit" class="w-full p-2">
        <div class="mb-3 text-lg font-medium text-center text-gray-900">
            {{ props.link ? "Ubah" : "Tambah" }} Tautan
            {{ props.link.name || "Tautan Sosial" }}
        </div>

        <div class="flex flex-col items-start gap-3">
            <div class="flex flex-col items-start gap-1.5 w-full">
                <InputLabel for="url" value="URL" />
                <TextInput
                    id="url"
                    v-model="form.url"
                    type="text"
                    :placeholder="`Masukkan URL ${
                        props.link?.name || 'Media Sosial'
                    }`"
                    class="block w-full"
                    required
                    :autofocus="true"
                    :error="form.errors.url"
                    @update:modelValue="form.errors.url = null"
                />
            </div>
        </div>

        <div class="flex justify-center w-full gap-4 mt-6 text-base">
            <SecondaryButton type="button" @click="emit('close')">
                Batalkan
            </SecondaryButton>
            <PrimaryButton type="submit"> Simpan </PrimaryButton>
        </div>
    </form>
</template>

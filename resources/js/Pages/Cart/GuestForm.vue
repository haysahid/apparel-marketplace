<script setup lang="ts">
import InputGroup from "@/Components/InputGroup.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useOrderStore } from "@/stores/order-store";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    title: {
        type: String || null,
        default: "Data Pemesan",
    },
    isEdit: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["submit"]);

const orderStore = useOrderStore();

const form = useForm({
    guest_name: orderStore.form.guest_name,
    guest_email: orderStore.form.guest_email,
    guest_phone: orderStore.form.guest_phone,
});

function submit() {
    orderStore.updateForm({
        ...orderStore.form,
        ...form.data(),
    });

    emit("submit");
}
</script>

<template>
    <form @submit.prevent="submit" class="w-full">
        <div class="flex flex-col w-full gap-4 gap-y-4">
            <h3 v-if="props.title" class="text-lg font-semibold text-gray-700">
                {{ title }}
            </h3>

            <div class="flex flex-col gap-y-3">
                <InputGroup label="Nama Lengkap" id="name">
                    <TextInput
                        id="name"
                        v-model="form.guest_name"
                        type="text"
                        placeholder="Masukkan nama"
                        required
                        :autofocus="true"
                        autocomplete="name"
                    />
                </InputGroup>

                <InputGroup label="Email" id="email">
                    <TextInput
                        id="email"
                        v-model="form.guest_email"
                        type="email"
                        placeholder="Masukkan alamat email"
                        required
                        autocomplete="email"
                    />
                </InputGroup>

                <InputGroup label="No. Telepon (WA)" id="phone">
                    <TextInput
                        id="phone"
                        v-model="form.guest_phone"
                        type="text"
                        placeholder="Masukkan no. telepon (WA)"
                        required
                        autocomplete="phone"
                    />
                </InputGroup>
            </div>

            <PrimaryButton type="submit" class="mt-3">
                {{ isEdit ? "Simpan" : "Lanjutkan" }}
            </PrimaryButton>
        </div>
    </form>
</template>

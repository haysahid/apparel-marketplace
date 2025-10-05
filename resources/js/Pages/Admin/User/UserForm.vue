<script setup lang="ts">
import { ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ImageInput from "@/Components/ImageInput.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import InputGroup from "@/Components/InputGroup.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import CustomPageProps from "@/types/model/CustomPageProps";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
    isDialog: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["onSubmitted", "close"]);

const page = usePage<CustomPageProps>();
const roles = page.props.roles;

const form = useForm(
    props.user
        ? {
              ...props.user,
              role:
                  props.user.role ??
                  roles.find((role) => role.id === props.user?.role_id) ??
                  null,
          }
        : {
              name: null,
              username: null,
              email: null,
              password: null,
              password_confirmation: null,
              phone: null,
              address: null,
              avatar: null,
              role_id: null,

              // Relationships
              role: null,
          }
);

const submit = () => {
    if (props.user?.id) {
        form.transform((data) => {
            const formData = new FormData();
            Object.keys(data).forEach((key) => {
                if (key === "avatar" && !(data[key] instanceof File)) {
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
                partner: props.user,
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
                    <InputGroup id="name" label="Nama">
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Masukkan Nama"
                            required
                            :autofocus="true"
                            :error="form.errors.name"
                            @update:modelValue="form.errors.name = null"
                        />
                    </InputGroup>

                    <!-- Username -->
                    <InputGroup id="username" label="Username">
                        <TextInput
                            id="username"
                            v-model="form.username"
                            type="text"
                            placeholder="Masukkan Username"
                            required
                            :error="form.errors.username"
                            @update:modelValue="form.errors.username = null"
                        />
                    </InputGroup>

                    <!-- Email -->
                    <InputGroup id="email" label="Email Mitra">
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="Masukkan Email Mitra"
                            required
                            :error="form.errors.email"
                            @update:modelValue="form.errors.email = null"
                        />
                    </InputGroup>

                    <!-- Phone -->
                    <InputGroup id="phone" label="Telepon Mitra">
                        <TextInput
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            placeholder="Masukkan Telepon Mitra"
                            required
                            :error="form.errors.phone"
                            @update:modelValue="form.errors.phone = null"
                        />
                    </InputGroup>

                    <!-- Address -->
                    <InputGroup id="address" label="Alamat">
                        <TextAreaInput
                            id="address"
                            v-model="form.address"
                            type="text"
                            placeholder="Masukkan Alamat"
                            autocomplete="address"
                            :error="form.errors.address"
                            @update:modelValue="form.errors.address = null"
                        />
                    </InputGroup>

                    <!-- Avatar -->
                    <InputGroup id="avatar" label="Avatar">
                        <ImageInput
                            id="avatar"
                            v-model="form.avatar"
                            type="file"
                            accept="image/*"
                            placeholder="Upload Avatar"
                            width="max-w-[120px]"
                            height="h-[120px]"
                            :error="form.errors.avatar"
                            @update:modelValue="form.errors.avatar = null"
                        />
                    </InputGroup>

                    <!-- Role -->
                    <InputGroup id="role_id" label="Role">
                        <DropdownSearchInput
                            id="role_id"
                            :modelValue="
                                form.role_id
                                    ? {
                                          label: form.role?.name,
                                          value: form.role_id,
                                      }
                                    : null
                            "
                            :options="
                                roles.map((role) => ({
                                    value: role.id,
                                    label: role.name,
                                }))
                            "
                            :searchable="true"
                            placeholder="Pilih Role"
                            :error="form.errors.role_id"
                            @update:modelValue="
                                (value) => {
                                    form.errors.role_id = null;
                                    form.role_id = value ? value.value : null;
                                    form.role = value
                                        ? roles.find(
                                              (role) => role.id === value.value
                                          )
                                        : null;
                                }
                            "
                        />
                    </InputGroup>

                    <!-- Password -->
                    <InputGroup id="password" label="Password">
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="Masukkan Password"
                            :error="form.errors.password"
                            @update:modelValue="form.errors.password = null"
                        />
                    </InputGroup>

                    <!-- Confirm Password -->
                    <InputGroup
                        id="password_confirmation"
                        label="Konfirmasi Password"
                    >
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="Masukkan Konfirmasi Password"
                            :error="form.errors.password_confirmation"
                            @update:modelValue="
                                form.errors.password_confirmation = null
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

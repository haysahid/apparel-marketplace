<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import LandingLayout from "@/Layouts/LandingLayout.vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    username: "",
    password: "",
    remember: false,
});

const submit = () => {
    console.log("Submitting form with data:", form);
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("admin.login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <LandingLayout title="Masuk Admin">
        <AuthenticationCard class="!min-h-[100vh] !pb-[200px]">
            <template #logo>
                <div
                    class="flex flex-col items-center justify-center gap-2 mb-6 sm:flex-row"
                >
                    <h1 class="text-2xl font-bold text-gray-800 sm:block">
                        Masuk Admin
                    </h1>
                </div>
            </template>

            <div
                v-if="status"
                class="mb-4 text-sm font-medium text-center text-green-600"
            >
                {{ status }}
            </div>

            <div
                v-if="form.errors.access"
                class="mb-4 text-sm font-medium text-center text-red-600"
            >
                {{ form.errors.access }}
            </div>

            <form @submit.prevent="submit">
                <div>
                    <TextInput
                        id="username"
                        v-model="form.username"
                        type="text"
                        placeholder="Masukkan username"
                        class="block w-full mt-1"
                        required
                        :autofocus="true"
                        autocomplete="username"
                        :error="form.errors.username"
                        @update:modelValue="form.errors.username = null"
                    >
                        <template #prefix>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="25"
                                viewBox="0 0 24 25"
                                class="absolute fill-primary left-2 size-5"
                            >
                                <path
                                    d="M12 10.5C14.2091 10.5 16 8.70914 16 6.5C16 4.29086 14.2091 2.5 12 2.5C9.79086 2.5 8 4.29086 8 6.5C8 8.70914 9.79086 10.5 12 10.5Z"
                                />
                                <path
                                    d="M20 16.5C20 19.5372 20 22 12 22C4 22 4 19.5372 4 16.5C4 13.4628 7.582 11 12 11C16.418 11 20 13.4628 20 16.5Z"
                                />
                            </svg>
                        </template>
                    </TextInput>
                </div>

                <div class="mt-4">
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="Masukkan password"
                        class="block w-full mt-1"
                        required
                        autocomplete="current-password"
                    >
                        <template #prefix>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="25"
                                viewBox="0 0 24 25"
                                class="absolute fill-primary left-2 size-5"
                            >
                                <path
                                    d="M12 17.5C12.5304 17.5 13.0391 17.2893 13.4142 16.9142C13.7893 16.5391 14 16.0304 14 15.5C14 14.9696 13.7893 14.4609 13.4142 14.0858C13.0391 13.7107 12.5304 13.5 12 13.5C11.4696 13.5 10.9609 13.7107 10.5858 14.0858C10.2107 14.4609 10 14.9696 10 15.5C10 16.0304 10.2107 16.5391 10.5858 16.9142C10.9609 17.2893 11.4696 17.5 12 17.5ZM18 8.5C18.5304 8.5 19.0391 8.71071 19.4142 9.08579C19.7893 9.46086 20 9.96957 20 10.5V20.5C20 21.0304 19.7893 21.5391 19.4142 21.9142C19.0391 22.2893 18.5304 22.5 18 22.5H6C5.46957 22.5 4.96086 22.2893 4.58579 21.9142C4.21071 21.5391 4 21.0304 4 20.5V10.5C4 9.96957 4.21071 9.46086 4.58579 9.08579C4.96086 8.71071 5.46957 8.5 6 8.5H7V6.5C7 5.17392 7.52678 3.90215 8.46447 2.96447C9.40215 2.02678 10.6739 1.5 12 1.5C12.6566 1.5 13.3068 1.62933 13.9134 1.8806C14.52 2.13188 15.0712 2.50017 15.5355 2.96447C15.9998 3.42876 16.3681 3.97995 16.6194 4.58658C16.8707 5.19321 17 5.84339 17 6.5V8.5H18ZM12 3.5C11.2044 3.5 10.4413 3.81607 9.87868 4.37868C9.31607 4.94129 9 5.70435 9 6.5V8.5H15V6.5C15 5.70435 14.6839 4.94129 14.1213 4.37868C13.5587 3.81607 12.7956 3.5 12 3.5Z"
                                />
                            </svg>
                        </template>
                    </TextInput>
                </div>

                <div class="flex items-center justify-center mt-8">
                    <PrimaryButton
                        class="w-full px-4 py-2"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Masuk
                    </PrimaryButton>
                </div>
            </form>
        </AuthenticationCard>
    </LandingLayout>
</template>

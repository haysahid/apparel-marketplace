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
    name: "",
    username: "",
    email: "",
    phone: "",
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        redirect: route().params.redirect,
    })).post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <LandingLayout title="Daftar">
        <AuthenticationCard class="!min-h-[100vh] !pb-[200px]">
            <template #logo>
                <div
                    class="flex flex-col items-center justify-center gap-2 mb-6 sm:flex-row"
                >
                    <h1 class="text-2xl font-bold text-gray-800 sm:block">
                        Daftar
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
                        id="name"
                        v-model="form.name"
                        type="text"
                        placeholder="Masukkan nama"
                        class="block w-full mt-1"
                        required
                        :autofocus="true"
                        autocomplete="name"
                        :error="form.errors.name"
                        @update:modelValue="form.errors.name = null"
                    >
                        <template #prefix>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="25"
                                viewBox="0 0 24 25"
                                class="absolute fill-primary left-3"
                            >
                                <path
                                    d="M11 2.5C9.67392 2.5 8.40215 3.02678 7.46447 3.96447C6.52678 4.90215 6 6.17392 6 7.5C6 8.82608 6.52678 10.0979 7.46447 11.0355C8.40215 11.9732 9.67392 12.5 11 12.5C12.3261 12.5 13.5979 11.9732 14.5355 11.0355C15.4732 10.0979 16 8.82608 16 7.5C16 6.17392 15.4732 4.90215 14.5355 3.96447C13.5979 3.02678 12.3261 2.5 11 2.5ZM11 13.5C11.5913 13.5013 12.166 13.5413 12.724 13.62C12.9116 13.6462 13.088 13.7252 13.2325 13.8478C13.377 13.9703 13.4837 14.1314 13.5401 14.3123C13.5966 14.4931 13.6006 14.6863 13.5515 14.8693C13.5024 15.0524 13.4024 15.2177 13.263 15.346C12.44 16.0999 11.8098 17.0403 11.4252 18.0881C11.0407 19.1359 10.9131 20.2607 11.053 21.368C11.0707 21.5103 11.0575 21.6547 11.0145 21.7915C10.9714 21.9282 10.8995 22.0541 10.8035 22.1606C10.7075 22.2671 10.5897 22.3518 10.4582 22.4088C10.3266 22.4658 10.1843 22.4938 10.041 22.491C8.031 22.451 6.151 22.275 4.747 21.845C4.045 21.63 3.383 21.328 2.881 20.883C2.35 20.413 2 19.78 2 19C2 18.213 2.358 17.477 2.844 16.861C3.338 16.236 4.021 15.661 4.822 15.171C6.425 14.195 8.605 13.5 11 13.5ZM21.212 14.534C21.6807 15.0028 21.944 15.6386 21.944 16.3015C21.944 16.9644 21.6807 17.6002 21.212 18.069L17.794 21.487C17.5646 21.7163 17.2671 21.8651 16.946 21.911L14.637 22.241C14.4831 22.2631 14.3262 22.2491 14.1787 22.1999C14.0312 22.1508 13.8972 22.068 13.7873 21.958C13.6774 21.848 13.5947 21.7139 13.5457 21.5664C13.4967 21.4188 13.4827 21.2619 13.505 21.108L13.835 18.8C13.8807 18.4786 14.0295 18.1807 14.259 17.951L17.677 14.533C18.1458 14.0643 18.7816 13.801 19.4445 13.801C20.1074 13.801 20.7432 14.0643 21.212 14.533V14.534Z"
                                />
                            </svg>
                        </template>
                    </TextInput>
                </div>

                <div class="mt-4">
                    <TextInput
                        id="username"
                        v-model="form.username"
                        type="text"
                        placeholder="Masukkan username"
                        class="block w-full mt-1"
                        required
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
                                class="absolute fill-primary left-3"
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
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="Masukkan alamat email"
                        class="block w-full mt-1"
                        required
                        autocomplete="email"
                        :error="form.errors.email"
                        @update:modelValue="form.errors.email = null"
                    >
                        <template #prefix>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="25"
                                viewBox="0 0 24 25"
                                class="absolute fill-primary left-3"
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
                        id="phone"
                        v-model="form.phone"
                        type="text"
                        placeholder="Masukkan no. telepon (WA)"
                        class="block w-full mt-1"
                        required
                        autocomplete="phone"
                        :error="form.errors.phone"
                        @update:modelValue="form.errors.phone = null"
                    >
                        <template #prefix>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="25"
                                viewBox="0 0 24 25"
                                class="absolute fill-primary left-3"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M12 2.5C6.477 2.5 2 6.977 2 12.5C2 14.39 2.525 16.16 3.438 17.668L2.546 20.7C2.49478 20.8741 2.49141 21.0587 2.53624 21.2346C2.58107 21.4104 2.67245 21.5709 2.80076 21.6992C2.92907 21.8276 3.08958 21.9189 3.26542 21.9638C3.44125 22.0086 3.62592 22.0052 3.8 21.954L6.832 21.062C8.39074 22.0049 10.1782 22.5023 12 22.5C17.523 22.5 22 18.023 22 12.5C22 6.977 17.523 2.5 12 2.5ZM9.738 14.763C11.761 16.785 13.692 17.052 14.374 17.077C15.411 17.115 16.421 16.323 16.814 15.404C16.8632 15.2896 16.881 15.1641 16.8655 15.0405C16.85 14.917 16.8019 14.7998 16.726 14.701C16.178 14.001 15.437 13.498 14.713 12.998C14.5619 12.8932 14.3761 12.8512 14.1946 12.8806C14.0131 12.9101 13.8502 13.0088 13.74 13.156L13.14 14.071C13.1083 14.12 13.0591 14.1551 13.0025 14.1692C12.9459 14.1833 12.886 14.1754 12.835 14.147C12.428 13.914 11.835 13.518 11.409 13.092C10.983 12.666 10.611 12.1 10.402 11.719C10.3767 11.6705 10.3696 11.6145 10.3819 11.5611C10.3941 11.5078 10.425 11.4606 10.469 11.428L11.393 10.742C11.5252 10.6276 11.6106 10.4684 11.6328 10.2949C11.6549 10.1215 11.6123 9.94596 11.513 9.802C11.065 9.146 10.543 8.312 9.786 7.759C9.6881 7.68866 9.57369 7.64479 9.45385 7.63165C9.33402 7.61851 9.21282 7.63654 9.102 7.684C8.182 8.078 7.386 9.088 7.424 10.127C7.449 10.809 7.716 12.74 9.738 14.763Z"
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
                        :error="form.errors.password"
                        @update:modelValue="form.errors.password = null"
                        required
                    >
                        <template #prefix>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="25"
                                viewBox="0 0 24 25"
                                class="absolute fill-primary left-3"
                            >
                                <path
                                    d="M12 17.5C12.5304 17.5 13.0391 17.2893 13.4142 16.9142C13.7893 16.5391 14 16.0304 14 15.5C14 14.9696 13.7893 14.4609 13.4142 14.0858C13.0391 13.7107 12.5304 13.5 12 13.5C11.4696 13.5 10.9609 13.7107 10.5858 14.0858C10.2107 14.4609 10 14.9696 10 15.5C10 16.0304 10.2107 16.5391 10.5858 16.9142C10.9609 17.2893 11.4696 17.5 12 17.5ZM18 8.5C18.5304 8.5 19.0391 8.71071 19.4142 9.08579C19.7893 9.46086 20 9.96957 20 10.5V20.5C20 21.0304 19.7893 21.5391 19.4142 21.9142C19.0391 22.2893 18.5304 22.5 18 22.5H6C5.46957 22.5 4.96086 22.2893 4.58579 21.9142C4.21071 21.5391 4 21.0304 4 20.5V10.5C4 9.96957 4.21071 9.46086 4.58579 9.08579C4.96086 8.71071 5.46957 8.5 6 8.5H7V6.5C7 5.17392 7.52678 3.90215 8.46447 2.96447C9.40215 2.02678 10.6739 1.5 12 1.5C12.6566 1.5 13.3068 1.62933 13.9134 1.8806C14.52 2.13188 15.0712 2.50017 15.5355 2.96447C15.9998 3.42876 16.3681 3.97995 16.6194 4.58658C16.8707 5.19321 17 5.84339 17 6.5V8.5H18ZM12 3.5C11.2044 3.5 10.4413 3.81607 9.87868 4.37868C9.31607 4.94129 9 5.70435 9 6.5V8.5H15V6.5C15 5.70435 14.6839 4.94129 14.1213 4.37868C13.5587 3.81607 12.7956 3.5 12 3.5Z"
                                />
                            </svg>
                        </template>
                    </TextInput>
                </div>

                <div class="mt-4">
                    <TextInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        placeholder="Masukkan konfirmasi password"
                        class="block w-full mt-1"
                        :error="form.errors.password_confirmation"
                        @update:modelValue="
                            form.errors.password_confirmation = null
                        "
                        required
                    >
                        <template #prefix>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="25"
                                viewBox="0 0 24 25"
                                class="absolute fill-primary left-3"
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
                        class="px-4 py-2 w-full max-w-[240px]"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Daftar
                    </PrimaryButton>
                </div>
            </form>

            <!-- Login -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Sudah punya akun?
                    <Link
                        :href="
                            route('login', {
                                redirect: route().params.redirect,
                            })
                        "
                        class="font-medium text-primary hover:underline"
                    >
                        Masuk disini
                    </Link>
                </p>
            </div>
        </AuthenticationCard>
    </LandingLayout>
</template>

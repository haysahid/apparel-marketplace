<script setup lang="ts">
import DefaultCard from "@/Components/DefaultCard.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import UserCard from "@/Pages/Admin/User/UserCard.vue";
import userService from "@/services/admin/user-service";
import adminStoreService from "@/services/admin/store-service";
import storeService from "@/services/my-store/store-service";
import { computed, nextTick, onMounted, ref } from "vue";
import useDebounce from "@/plugins/debounce";
import UserRolePairCard from "./UserRolePairCard.vue";
import SelectInput from "@/Components/SelectInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    storeId: {
        type: Number,
        default: null,
    },
    userRolePairs: Array as () => UserRolePair[],
    roles: Array as () => RoleEntity[],
    isModal: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits({
    submitted: () => true,
    close: () => true,
});

const form = ref<UserRolePair[]>([]);

const users = ref<UserEntity[]>([]);
const filteredUsers = computed(() => {
    if (props.userRolePairs.length === 0) {
        return users.value || [];
    }

    const selectedUserIds = [
        ...props.userRolePairs.map((item) => item.user.id),
        ...form.value.map((item) => item.user.id),
    ];
    return (
        users.value.filter((user) => !selectedUserIds.includes(user.id)) || []
    );
});

const searchDebounce = useDebounce();

function getUsers(search?: string) {
    userService().getUsers(
        {
            search: search || undefined,
            orderBy: "name",
        },
        {
            onSuccess: (response) => {
                users.value = response.data.result.data;
            },
        }
    );
}

function validate(): boolean {
    let isValid = true;

    for (const item of form.value) {
        if (item.role === null) {
            isValid = false;
            break;
        }
    }

    return isValid;
}

const submitStatus = ref(null);
function submit() {
    if (!validate()) {
        openErrorDialog("Peran harus dipilih untuk setiap pengguna.");
        return;
    }

    submitStatus.value = "loading";
    const userRolesToSubmit = form.value
        .filter(
            (item) =>
                item.role !== null &&
                !props.userRolePairs.some(
                    (userRole) => userRole.user.id === item.user.id
                )
        )
        .map((item) => ({
            user_id: item.user.id,
            role_slug: item.role.slug,
        }));

    for (const userRole of userRolesToSubmit) {
        if (!props.storeId) {
            storeService().addUserRole(
                {
                    userId: userRole.user_id,
                    roleSlug: userRole.role_slug,
                },
                {
                    autoShowDialog: false,
                    onSuccess: () => {
                        submitStatus.value = "success";
                        emit("submitted");
                    },
                    onError: (error) => {
                        console.error("Error adding user to store:", error);
                        submitStatus.value = "error";
                        openErrorDialog("Gagal menambahkan pengguna ke toko.");
                    },
                }
            );
        } else {
            adminStoreService().addUserRole(
                {
                    storeId: props.storeId,
                    userId: userRole.user_id,
                    roleSlug: userRole.role_slug,
                },
                {
                    autoShowDialog: false,
                    onSuccess: () => {
                        submitStatus.value = "success";
                        emit("submitted");
                    },
                    onError: (error) => {
                        console.error("Error adding user to store:", error);
                        submitStatus.value = "error";
                        openErrorDialog("Gagal menambahkan pengguna ke toko.");
                    },
                }
            );
        }
    }
}

const showErrorDialog = ref(false);
const errorMessage = ref(null);
function openErrorDialog(message: string) {
    errorMessage.value = message;
    showErrorDialog.value = true;
}

onMounted(() => {
    getUsers();

    nextTick(() => {
        const searchInput = document.getElementById(
            "text-input-search-user"
        ) as HTMLInputElement | null;
        if (searchInput) {
            searchInput.focus();
        }
    });
});
</script>

<template>
    <DefaultCard
        :isMain="true"
        class="flex flex-col w-full min-h-[80vh] gap-4 !px-0 py-4 transition-all duration-300 ease-in-out"
    >
        <div
            class="flex items-center justify-between gap-4 px-4 bg-white sm:px-6"
        >
            <h2 class="font-semibold text-gray-800 text-nowrap">
                Pilih Pengguna
            </h2>

            <div class="flex items-center gap-2">
                <SecondaryButton
                    v-if="props.isModal"
                    @click="emit('close')"
                    class="px-3 py-1 text-sm"
                >
                    Batal
                </SecondaryButton>
                <PrimaryButton
                    :disabled="submitStatus === 'loading' || form.length === 0"
                    @click="submit"
                >
                    <span v-if="submitStatus !== 'loading'">Simpan</span>
                    <span v-else>Memproses...</span>
                </PrimaryButton>
            </div>
        </div>

        <div class="w-full px-4 sm:px-6">
            <DropdownSearchInput
                id="search-user"
                :modelValue="null"
                :options="
                    filteredUsers.map((user) => ({
                        label: user.name,
                        value: user.id,
                    }))
                "
                placeholder="Cari pengguna..."
                :autoResize="true"
                @search="
                    (search) => searchDebounce(() => getUsers(search), 500)
                "
                @clear="getUsers()"
            >
                <template #options>
                    <ul
                        v-if="filteredUsers.length"
                        class="flex flex-col w-full divide-y"
                    >
                        <li v-for="user in filteredUsers" :key="user.id">
                            <UserCard
                                :user="user"
                                class="border-none rounded-none cursor-pointer hover:border-none hover:!ring-0 hover:bg-gray-50"
                                @click="
                                    form.unshift({ user: user, role: null });
                                    getUsers();
                                "
                            />
                        </li>
                    </ul>
                    <p v-else class="p-4 text-sm text-center text-gray-500">
                        Data tidak ditemukan.
                    </p>
                </template>
            </DropdownSearchInput>
        </div>

        <!-- Selected Users -->
        <div class="flex flex-col w-full gap-4 px-4 sm:px-6">
            <div v-if="form.length" class="flex flex-col w-full gap-2">
                <UserRolePairCard
                    v-for="(userRole, index) in form"
                    :key="userRole.user.id"
                    :userRolePair="userRole"
                >
                    <template #trailing>
                        <div class="flex items-center gap-2.5">
                            <SelectInput
                                class="w-full"
                                :options="
                                    props.roles.map((role) => ({
                                        label: role.name,
                                        value: role.slug,
                                        disabled:
                                            role.slug === 'super-admin' ||
                                            role.slug === 'guest',
                                    }))
                                "
                                :modelValue="userRole.role?.slug"
                                :disabled="
                                    userRole.user?.id ===
                                        $page.props.auth.user.id ||
                                    userRole.role?.slug === 'super-admin' ||
                                    ($page.props.selected_store_role.slug !==
                                        'admin' &&
                                        $page.props.selected_store_role.slug !==
                                            'store-owner')
                                "
                                placeholder="Pilih Peran"
                                @update:modelValue="
                                    (value) => {
                                        userRole.role = props.roles.find(
                                            (role) => role.slug === value
                                        );
                                    }
                                "
                            />
                            <button
                                type="button"
                                class="text-gray-500 hover:text-red-500"
                                @click="
                                    form.splice(index, 1);
                                    getUsers();
                                "
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    class="size-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </template>
                </UserRolePairCard>
            </div>
            <p v-else class="py-4 text-sm text-center text-gray-500">
                Belum ada pengguna yang dipilih.
            </p>
        </div>

        <ErrorDialog :show="showErrorDialog" @close="showErrorDialog = false">
            <template #content>
                <div>
                    <div
                        class="mb-1 text-lg font-semibold text-center text-gray-900"
                    >
                        Terjadi Kesalahan
                    </div>
                    <p class="text-sm text-center text-gray-700">
                        {{ errorMessage }}
                    </p>
                </div>
            </template>
        </ErrorDialog>
    </DefaultCard>
</template>

<script setup lang="ts">
import DefaultCard from "@/Components/DefaultCard.vue";
import DialogModal from "@/Components/DialogModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ThreeDotsLoading from "@/Components/ThreeDotsLoading.vue";
import { computed, ref } from "vue";
import AddStoreUserForm from "./AddStoreUserForm.vue";
import UserRolePairCard from "./UserRolePairCard.vue";
import SelectInput from "@/Components/SelectInput.vue";
import storeService from "@/services/my-store/store-service";
import adminStoreService from "@/services/admin/store-service";
import { router, usePage } from "@inertiajs/vue3";
import { useDialogStore } from "@/stores/dialog-store";
import CustomPageProps from "@/types/model/CustomPageProps";

const props = defineProps({
    storeId: {
        type: Number,
        default: null,
    },
    userRolePairs: Array as () => UserRolePair[],
    roles: Array as () => RoleEntity[],
    isLoading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits({
    reload: () => true,
});

const showAddUserModal = ref(false);

const addOrUpdateUserRoleStatus = ref(null);

function updateUserRole(data: { userId: number; roleSlug: string }) {
    if (!props.storeId) {
        storeService().updateUserRole(data, {
            onChangeStatus: (status) =>
                (addOrUpdateUserRoleStatus.value = status),
        });
    } else {
        adminStoreService().updateUserRole(
            {
                storeId: props.storeId,
                ...data,
            },
            {
                onChangeStatus: (status) =>
                    (addOrUpdateUserRoleStatus.value = status),
            }
        );
    }
}

function reloadUserRolePairs() {
    useDialogStore().openSuccessDialog("Peran pengguna berhasil diperbarui.");
    emit("reload");
}

const page = usePage<CustomPageProps>();

function canChangeRole(userRolePair: UserRolePair): boolean {
    const selectedStoreRole = page.props.selected_store_role;

    // Prevent changing own role
    if (userRolePair.user.id === page.props.auth.user.id) {
        return false;
    }

    // Prevent changing super-admin role
    if (userRolePair.role?.slug === "super-admin") {
        return false;
    }

    // Only allow if current user is admin or store-owner or global admin
    if (
        selectedStoreRole?.slug === "admin" ||
        selectedStoreRole?.slug === "store-owner" ||
        page.props.auth.is_admin
    ) {
        return true;
    }

    // Allow if current user is admin of the application
    if (page.props.auth.is_admin) {
        return true;
    }

    return false;
}

const filteredUserRolePairs = computed(() => {
    const selectedStoreRole = page.props.selected_store_role;

    // If current user is super-admin or global admin, show all user-role pairs
    if (selectedStoreRole?.slug === "super-admin" || page.props.auth.is_admin) {
        return props.userRolePairs;
    }

    // Otherwise, filter out super-admin user-role pairs
    return props.userRolePairs?.filter(
        (userRolePair) => userRolePair.role?.slug !== "super-admin"
    );
});
</script>

<template>
    <DefaultCard class="w-full">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">
                Pengguna ({{ filteredUserRolePairs?.length ?? 0 }})
            </h3>
            <PrimaryButton
                v-if="
                    ['super-admin', 'admin', 'store-owner'].includes(
                        $page.props.selected_store_role?.slug
                    ) || $page.props.auth.is_admin
                "
                @click="showAddUserModal = true"
            >
                Tambah
            </PrimaryButton>
        </div>
        <div class="w-full mt-2.5">
            <div
                v-if="filteredUserRolePairs && filteredUserRolePairs.length > 0"
                class="flex flex-col w-full gap-2"
            >
                <UserRolePairCard
                    v-for="(userRole, index) in filteredUserRolePairs"
                    :key="index"
                    :userRolePair="userRole"
                    :roles="props.roles"
                >
                    <template #trailing>
                        <SelectInput
                            :options="
                                props.roles.map((role) => ({
                                    label: role.name,
                                    value: role.slug,
                                    disabled: [
                                        'super-admin',
                                        'user',
                                        'guest',
                                    ].includes(role.slug),
                                }))
                            "
                            :modelValue="userRole.role?.slug"
                            :disabled="
                                !canChangeRole(userRole) ||
                                addOrUpdateUserRoleStatus === 'loading'
                            "
                            @update:modelValue="(value) =>
                                updateUserRole({
                                    userId: userRole.user.id,
                                    roleSlug: value as string,
                                })"
                        />
                    </template>
                </UserRolePairCard>
            </div>
            <div v-else class="flex items-center justify-center h-[10vh] mb-6">
                <ThreeDotsLoading v-if="isLoading" />
                <p v-else class="text-sm text-center text-gray-500">
                    Data tidak ditemukan.
                </p>
            </div>
        </div>

        <!-- Add User Modal -->
        <DialogModal
            :show="showAddUserModal"
            title="Tambah Pengguna"
            @close="showAddUserModal = false"
            maxWidth="lg"
            dialogClass="overflow-y-hidden"
            containerClass="!p-0"
        >
            <template #content>
                <AddStoreUserForm
                    :isModal="true"
                    :storeId="props.storeId"
                    :userRolePairs="props.userRolePairs"
                    :roles="props.roles"
                    @submitted="
                        showAddUserModal = false;
                        reloadUserRolePairs();
                    "
                    @close="showAddUserModal = false"
                />
            </template>
        </DialogModal>
    </DefaultCard>
</template>

import cookieManager from "@/plugins/cookie-manager";
import { useDialogStore } from "@/stores/dialog-store";
import axios from "axios";

export default function storeService() {
    const dialogStore = useDialogStore();
    const token = `Bearer ${cookieManager.getItem("access_token")}`;
    const selectedStoreId = cookieManager.getItem("selected_store_id");

    async function addUserRole(
        { storeId, userId, roleSlug },
        {
            autoShowDialog = true,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        }
    ) {
        onChangeStatus("loading");
        await axios
            .post(
                `/api/admin/store/${storeId}/user-role`,
                {
                    user_id: userId,
                    role_slug: roleSlug,
                },
                {
                    headers: {
                        Authorization: token,
                        "X-Selected-Store-ID": selectedStoreId,
                    },
                }
            )
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Pengguna berhasil ditambahkan ke toko."
                    );
                }
            })
            .catch((error) => {
                console.error("Error adding user role:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat menambahkan pengguna ke toko."
                    );
                }
            });
    }

    async function updateUserRole(
        { storeId, userId, roleSlug },
        {
            autoShowDialog = true,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        }
    ) {
        onChangeStatus("loading");
        axios
            .put(
                `/api/admin/store/${storeId}/user-role`,
                {
                    user_id: userId,
                    role_slug: roleSlug,
                },
                {
                    headers: {
                        Authorization: token,
                        "X-Selected-Store-ID": selectedStoreId,
                    },
                }
            )
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Peran pengguna berhasil diperbarui."
                    );
                }
            })
            .catch((error) => {
                console.error("Error updating user role:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat memperbarui peran pengguna di toko."
                    );
                }
            });
    }

    async function removeUserRole(
        { storeId, userId, roleSlug },
        {
            autoShowDialog = true,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        }
    ) {
        onChangeStatus("loading");
        axios
            .delete(`/api/admin/store/${storeId}/user-role`, {
                data: {
                    user_id: userId,
                    role_slug: roleSlug,
                },
                headers: {
                    Authorization: token,
                    "X-Selected-Store-ID": selectedStoreId,
                },
            })
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Peran pengguna berhasil dihapus dari toko."
                    );
                }
            })
            .catch((error) => {
                console.error("Error removing user role:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat menghapus peran pengguna dari toko."
                    );
                }
            });
    }

    return {
        addUserRole,
        updateUserRole,
        removeUserRole,
    };
}

import cookieManager from "@/plugins/cookie-manager";
import { useDialogStore } from "@/stores/dialog-store";
import axios from "axios";

export default function storeService() {
    const dialogStore = useDialogStore();
    const token = `Bearer ${cookieManager.getItem("access_token")}`;
    const selectedStoreId = cookieManager.getItem("selected_store_id");

    async function addUserRole(
        { userId, roleSlug },
        {
            autoShowDialog = true,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");
        await axios
            .post(
                `/api/my-store/user/${userId}/role`,
                {
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
        { userId, roleSlug },
        {
            autoShowDialog = true,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");
        await axios
            .put(
                `/api/my-store/user/${userId}/role`,
                {
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
                            "Terjadi kesalahan saat memperbarui peran pengguna."
                    );
                }
            });
    }

    async function removeUserRole(
        { userId, roleSlug },
        {
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");
        await axios
            .delete(`/api/my-store/user/${userId}/role`, {
                data: { role_slug: roleSlug },
                headers: {
                    Authorization: token,
                    "X-Selected-Store-ID": selectedStoreId,
                },
            })
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                dialogStore.openSuccessDialog(
                    response.data.meta.message ||
                        "Peran pengguna berhasil dihapus dari toko."
                );
            })
            .catch((error) => {
                console.error("Error deleting user role:", error);
                onChangeStatus("error");
                onError(error);
                dialogStore.openErrorDialog(
                    error.response?.data?.meta?.message ||
                        "Terjadi kesalahan saat menghapus peran pengguna dari toko."
                );
            });
    }

    return {
        addUserRole,
        updateUserRole,
        removeUserRole,
    };
}

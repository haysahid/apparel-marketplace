import cookieManager from "@/plugins/cookie-manager";
import { useDialogStore } from "@/stores/dialog-store";
import axios from "axios";

export default function myStoreService() {
    const dialogStore = useDialogStore();
    const token = `Bearer ${cookieManager.getItem("access_token")}`;
    const selectedStoreId = cookieManager.getItem("selected_store_id");

    async function addStoreLogo(
        { file },
        {
            autoShowDialog = true,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");
        const formData = new FormData();
        formData.append("logo", file);

        await axios
            .post(`/api/my-store/store/logo`, formData, {
                headers: {
                    Authorization: token,
                    "X-Selected-Store-ID": selectedStoreId,
                    "Content-Type": "multipart/form-data",
                },
            })
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Logo toko berhasil diperbarui."
                    );
                }
            })
            .catch((error) => {
                console.error("Error uploading store logo:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat mengunggah logo toko."
                    );
                }
            });
    }

    async function updateStoreLogo(
        { file },
        {
            autoShowDialog = true,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");
        const formData = new FormData();
        formData.append("logo", file);
        formData.append("_method", "PUT");

        await axios
            .post(`/api/my-store/store/logo`, formData, {
                headers: {
                    Authorization: token,
                    "X-Selected-Store-ID": selectedStoreId,
                    "Content-Type": "multipart/form-data",
                },
            })
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Logo toko berhasil diperbarui."
                    );
                }
            })
            .catch((error) => {
                console.error("Error updating store logo:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat memperbarui logo toko."
                    );
                }
            });
    }

    async function deleteStoreLogo(
        {},
        {
            autoShowDialog = true,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");
        await axios
            .delete(`/api/my-store/store/logo`, {
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
                            "Logo toko berhasil dihapus."
                    );
                }
            })
            .catch((error) => {
                console.error("Error deleting store logo:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat menghapus logo toko."
                    );
                }
            });
    }

    async function addStoreBanner(
        { file },
        {
            autoShowDialog = true,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");
        const formData = new FormData();
        formData.append("banner", file);

        await axios
            .post(`/api/my-store/store/banner`, formData, {
                headers: {
                    Authorization: token,
                    "X-Selected-Store-ID": selectedStoreId,
                    "Content-Type": "multipart/form-data",
                },
            })
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Banner toko berhasil diperbarui."
                    );
                }
            })
            .catch((error) => {
                console.error("Error uploading store banner:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat mengunggah banner toko."
                    );
                }
            });
    }

    async function updateStoreBanner(
        { file },
        {
            autoShowDialog = true,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");
        const formData = new FormData();
        formData.append("banner", file);
        formData.append("_method", "PUT");

        await axios
            .post(`/api/my-store/store/banner`, formData, {
                headers: {
                    Authorization: token,
                    "X-Selected-Store-ID": selectedStoreId,
                    "Content-Type": "multipart/form-data",
                },
            })
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Banner toko berhasil diperbarui."
                    );
                }
            })
            .catch((error) => {
                console.error("Error updating store banner:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat memperbarui banner toko."
                    );
                }
            });
    }

    async function deleteStoreBanner(
        {},
        {
            autoShowDialog = true,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");
        await axios
            .delete(`/api/my-store/store/banner`, {
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
                            "Banner toko berhasil dihapus."
                    );
                }
            })
            .catch((error) => {
                console.error("Error deleting store banner:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat menghapus banner toko."
                    );
                }
            });
    }

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
        // Store Logo
        addStoreLogo,
        updateStoreLogo,
        deleteStoreLogo,

        // Store Banner
        addStoreBanner,
        updateStoreBanner,
        deleteStoreBanner,

        // Store User Role
        addUserRole,
        updateUserRole,
        removeUserRole,
    };
}

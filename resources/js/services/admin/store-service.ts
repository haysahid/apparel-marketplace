import cookieManager from "@/plugins/cookie-manager";
import { useDialogStore } from "@/stores/dialog-store";
import axios from "axios";

export default function adminStoreService() {
    const dialogStore = useDialogStore();
    const token = `Bearer ${cookieManager.getItem("access_token")}`;

    async function addStoreLogo(
        { storeId, file },
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
            .post(`/api/admin/store/${storeId}/logo`, formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                    Authorization: token,
                },
            })
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Logo toko berhasil ditambahkan."
                    );
                }
            })
            .catch((error) => {
                console.error("Error adding store logo:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat menambahkan logo toko."
                    );
                }
            });
    }

    async function updateStoreLogo(
        { storeId, file },
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
            .post(`/api/admin/store/${storeId}/logo`, formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                    Authorization: token,
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
        { storeId },
        {
            autoShowDialog = true,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");
        await axios
            .delete(`/api/admin/store/${storeId}/logo`, {
                headers: {
                    Authorization: token,
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
        { storeId, file },
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
            .post(`/api/admin/store/${storeId}/banner`, formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                    Authorization: token,
                },
            })
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Banner toko berhasil ditambahkan."
                    );
                }
            })
            .catch((error) => {
                console.error("Error adding store banner:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat menambahkan banner toko."
                    );
                }
            });
    }

    async function updateStoreBanner(
        { storeId, file },
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
            .post(`/api/admin/store/${storeId}/banner`, formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                    Authorization: token,
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
        { storeId },
        {
            autoShowDialog = true,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");
        await axios
            .delete(`/api/admin/store/${storeId}/banner`, {
                headers: {
                    Authorization: token,
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
        { storeId, userId, roleSlug },
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
                `/api/admin/store/${storeId}/user-role`,
                {
                    user_id: userId,
                    role_slug: roleSlug,
                },
                {
                    headers: {
                        Authorization: token,
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
        } = {}
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
        } = {}
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

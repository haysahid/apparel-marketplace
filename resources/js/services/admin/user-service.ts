import cookieManager from "@/plugins/cookie-manager";
import { useDialogStore } from "@/stores/dialog-store";
import axios from "axios";

export default function userService() {
    const dialogStore = useDialogStore();
    const token = `Bearer ${cookieManager.getItem("access_token")}`;
    const selectedStoreId = cookieManager.getItem("selected_store_id");

    async function getUsers(
        {
            page = undefined,
            search = undefined,
            limit = undefined,
            orderBy = undefined,
        } = {},
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");
        await axios
            .get("/api/admin/user", {
                params: {
                    page,
                    search,
                    limit,
                    order_by: orderBy,
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
                            "Data pengguna berhasil diambil."
                    );
                }
            })
            .catch((error) => {
                console.error("Error fetching users:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat mengambil data pengguna."
                    );
                }
            });
    }

    return {
        getUsers,
    };
}

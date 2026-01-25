import cookieManager from "@/plugins/cookie-manager";
import { useDialogStore } from "@/stores/dialog-store";
import axios from "axios";

export default function invoiceService() {
    const dialogStore = useDialogStore();
    const token = `Bearer ${cookieManager.getItem("access_token")}`;

    async function getShipments(
        invoiceId: number,
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {},
    ) {
        onChangeStatus("loading");

        await axios
            .get(`/api/shipment-dropdown-guest`, {
                params: {
                    invoice_id: invoiceId,
                },
                headers: {
                    Authorization: token,
                },
            })
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
            })
            .catch((error) => {
                console.error("Error fetching invoice shipments:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat mengambil data pengiriman invoice.",
                    );
                }
            });
    }

    return {
        getShipments,
    };
}

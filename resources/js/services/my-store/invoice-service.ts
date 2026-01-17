import cookieManager from "@/plugins/cookie-manager";
import { useDialogStore } from "@/stores/dialog-store";
import axios from "axios";

export default function invoiceService() {
    const dialogStore = useDialogStore();
    const token = `Bearer ${cookieManager.getItem("access_token")}`;
    const selectedStoreId = cookieManager.getItem("selected_store_id");

    async function changeStatus(
        { invoiceId, newStatus },
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");

        await axios
            .put(
                "/api/my-store/change-order-status",
                {
                    invoice_id: invoiceId,
                    status: newStatus,
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
                            "Status transaksi berhasil diubah."
                    );
                }
            })
            .catch((error) => {
                console.error("Error changing order status:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat mengubah status transaksi."
                    );
                }
            });
    }

    async function setDelivering(
        {
            invoiceId,
            shipments,
        }: { invoiceId: number; shipments: ShipmentEntity[] },
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        if (!shipments || shipments.length === 0) {
            dialogStore.openErrorDialog("Data pengiriman harus diisi.");
            return;
        }

        onChangeStatus("loading");
        await axios
            .post(
                `/api/my-store/set-invoice-delivering`,
                {
                    invoice_id: invoiceId,
                    shipments: shipments,
                },
                {
                    headers: {
                        Authorization: token,
                        Accept: "application/json",
                    },
                }
            )
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Invoice berhasil diubah menjadi diproses."
                    );
                }
            })
            .catch((error) => {
                console.error("Error setting invoice to processed:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat mengubah status invoice."
                    );
                }
            });
    }

    async function getShipments(
        invoiceId: number,
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {}
    ) {
        onChangeStatus("loading");

        await axios
            .get(`/api/my-store/shipment-dropdown`, {
                params: {
                    invoice_id: invoiceId,
                },
                headers: {
                    Authorization: token,
                    "X-Selected-Store-ID": selectedStoreId,
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
                            "Terjadi kesalahan saat mengambil data pengiriman invoice."
                    );
                }
            });
    }

    return {
        changeStatus,
        setDelivering,
        getShipments,
    };
}

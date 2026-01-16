import cookieManager from "@/plugins/cookie-manager";
import { useDialogStore } from "@/stores/dialog-store";
import axios from "axios";

export default function invoiceService() {
    const dialogStore = useDialogStore();
    const token = `Bearer ${cookieManager.getItem("access_token")}`;

    async function changeStatus(
        { invoiceId, newStatus },
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        }
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
        { invoiceId, trackingNumber },
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        }
    ) {
        if (!trackingNumber) {
            dialogStore.openErrorDialog("Nomor resi harus diisi.");
            return;
        }

        onChangeStatus("loading");
        await axios
            .post(
                `/api/my-store/set-invoice-delivering`,
                {
                    invoice_id: invoiceId,
                    tracking_number: trackingNumber,
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

    return {
        changeStatus,
        setDelivering,
    };
}

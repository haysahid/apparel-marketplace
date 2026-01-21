import cookieManager from "@/plugins/cookie-manager";
import { useDialogStore } from "@/stores/dialog-store";
import axios, { AxiosProgressEvent, CancelToken } from "axios";

export default function mediaService() {
    const dialogStore = useDialogStore();
    const token = `Bearer ${cookieManager.getItem("access_token")}`;
    const selectedStoreId = cookieManager.getItem("selected_store_id");

    async function getMediaList(
        {
            modelType,
            modelId = null,
            collectionName = "default",
            page = 1,
            perPage = 10,
            orderBy = "created_at",
            orderDirection = "desc",
        }: {
            modelType: string;
            modelId?: number;
            collectionName?: string;
            page?: number;
            perPage?: number;
            orderBy?: string;
            orderDirection?: "asc" | "desc";
        },
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {},
    ) {
        onChangeStatus("loading");

        await axios
            .get(`/api/my-store/media`, {
                headers: {
                    Authorization: token,
                    "X-Selected-Store-ID": selectedStoreId,
                },
                params: {
                    model_type: modelType,
                    model_id: modelId,
                    collection_name: collectionName,
                    page: page,
                    per_page: perPage,
                    order_by: orderBy,
                    order_direction: orderDirection,
                },
            })
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Daftar media berhasil diambil.",
                    );
                }
            })
            .catch((error) => {
                console.error("Error fetching media list:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat mengambil daftar media.",
                    );
                }
            });
    }

    async function uploadMedia(
        {
            modelType,
            file,
            collectionName = "default",
            cancelToken,
        }: {
            modelType: string;
            file: File;
            collectionName?: string;
            cancelToken?: CancelToken;
        },
        {
            autoShowDialog = true,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
            onProgress = (progressEvent: AxiosProgressEvent) => {},
        }: {
            autoShowDialog?: boolean;
            onSuccess?: (response: any) => void;
            onError?: (error: any) => void;
            onChangeStatus?: (status: string) => void;
            onProgress?: (progressEvent: AxiosProgressEvent) => void;
        } = {},
    ) {
        onChangeStatus("loading");
        const formData = new FormData();
        formData.append("model_type", modelType);
        formData.append("file", file);
        formData.append("collection_name", collectionName);

        await axios
            .post(`/api/my-store/media`, formData, {
                headers: {
                    Authorization: token,
                    "X-Selected-Store-ID": selectedStoreId,
                    "Content-Type": "multipart/form-data",
                },
                onUploadProgress: (progressEvent) => {
                    onProgress(progressEvent);
                },
                cancelToken: cancelToken,
            })
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Media berhasil diunggah.",
                    );
                }
            })
            .catch((error) => {
                if (axios.isCancel && axios.isCancel(error)) {
                    onChangeStatus("cancelled");
                    // Optionally handle cancel dialog
                } else {
                    console.error("Error uploading media:", error);
                    onChangeStatus("error");
                    onError(error);
                    if (autoShowDialog) {
                        dialogStore.openErrorDialog(
                            error.response?.data?.meta?.message ||
                                "Terjadi kesalahan saat mengunggah media.",
                        );
                    }
                }
            });
    }

    async function uploadMediaBulk(
        {
            modelType,
            files,
            collectionName = "default",
            cancelTokens,
        }: {
            modelType: string;
            modelId: number;
            files: File[];
            collectionName?: string;
            cancelTokens?: CancelToken[];
        },
        progressCallbacks = (
            progressEvent: AxiosProgressEvent,
            index: number,
        ) => {},
    ): Promise<void> {
        await Promise.all(
            files.map((file, index) =>
                uploadMedia(
                    {
                        modelType,
                        file,
                        collectionName,
                        cancelToken: cancelTokens
                            ? cancelTokens[index]
                            : undefined,
                    },
                    {
                        autoShowDialog: false,
                        onProgress: (progressEvent) => {
                            progressCallbacks(progressEvent, index);
                        },
                    },
                ),
            ),
        );
    }

    return {
        getMediaList,
        uploadMedia,
        uploadMediaBulk,
    };
}

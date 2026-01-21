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
            limit = 10,
            orderBy = "created_at",
            orderDirection = "desc",
            search = null,
        }: {
            modelType: string;
            modelId?: number;
            collectionName?: string;
            page?: number;
            limit?: number;
            orderBy?: string;
            orderDirection?: "asc" | "desc";
            search?: string | null;
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
                    limit: limit,
                    order_by: orderBy,
                    order_direction: orderDirection,
                    search: search,
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

    async function deleteMedia(
        mediaId: number,
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {},
    ) {
        onChangeStatus("loading");

        await axios
            .delete(`/api/my-store/media/${mediaId}`, {
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
                        response.data.meta.message || "Media berhasil dihapus.",
                    );
                }
            })
            .catch((error) => {
                console.error("Error deleting media:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat menghapus media.",
                    );
                }
            });
    }

    async function deleteMediaBulk(
        mediaIds: number[],
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {},
    ) {
        onChangeStatus("loading");

        await Promise.all(
            mediaIds.map((mediaId) =>
                deleteMedia(mediaId, {
                    autoShowDialog: false,
                }),
            ),
        )
            .then((responses) => {
                onChangeStatus("success");
                onSuccess(responses);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog("Media berhasil dihapus.");
                }
            })
            .catch((error) => {
                console.error("Error deleting media bulk:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        "Terjadi kesalahan saat menghapus media.",
                    );
                }
            });
    }

    async function uploadTemporaryMedia(
        file: File,
        {
            autoShowDialog = true,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
            onProgress = (progressEvent: AxiosProgressEvent) => {},
        } = {},
    ) {
        onChangeStatus("loading");
        const formData = new FormData();
        formData.append("file", file);

        await axios
            .post(`/api/my-store/temporary-media/upload`, formData, {
                headers: {
                    Authorization: token,
                    "X-Selected-Store-ID": selectedStoreId,
                    "Content-Type": "multipart/form-data",
                },
                onUploadProgress: (progressEvent) => {
                    onProgress(progressEvent);
                },
            })
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            "Media sementara berhasil diunggah.",
                    );
                }
            })
            .catch((error) => {
                console.error("Error uploading temporary media:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat mengunggah media sementara.",
                    );
                }
            });
    }

    async function uploadTemporaryMediaBulk(
        files: File[],
        {
            onProgress = (
                progressEvent: AxiosProgressEvent,
                index: number,
            ) => {},
            onSuccess = (
                uploadedTemporaryMediaList: TemporaryMediaEntity[],
            ) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {},
    ): Promise<void> {
        onChangeStatus("loading");

        let uploadedTemporaryMediaList: TemporaryMediaEntity[] = [];

        await Promise.all(
            files.map((file, index) =>
                uploadTemporaryMedia(file, {
                    autoShowDialog: false,
                    onProgress: (progressEvent) => {
                        onProgress(progressEvent, index);
                    },
                    onSuccess: (response) => {
                        uploadedTemporaryMediaList.push(response.data.result);
                    },
                }),
            ),
        );

        onChangeStatus("success");
        onSuccess(uploadedTemporaryMediaList);
    }

    async function getTemporaryMediaList(
        {} = {},
        {
            autoShowDialog = false,
            onSuccess = (response: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {},
    ) {
        onChangeStatus("loading");

        await axios
            .get(`/api/my-store/temporary-media`, {
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
                            "Daftar media sementara berhasil diambil.",
                    );
                }
            })
            .catch((error) => {
                console.error("Error fetching temporary media list:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            "Terjadi kesalahan saat mengambil daftar media sementara.",
                    );
                }
            });
    }

    return {
        getMediaList,
        uploadMedia,
        uploadMediaBulk,
        deleteMedia,
        deleteMediaBulk,
        getTemporaryMediaList,
        uploadTemporaryMedia,
        uploadTemporaryMediaBulk,
    };
}

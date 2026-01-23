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
            modelId,
            file,
            collectionName = "default",
            abortController,
        }: {
            modelType: string;
            modelId: number;
            file: File;
            collectionName?: string;
            abortController?: AbortController;
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
        formData.append("model_id", modelId.toString());
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
                signal: abortController ? abortController.signal : undefined,
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
            modelId,
            files,
            collectionName = "default",
            abortControllers,
            ignoreIndexes = [],
        }: {
            modelType: string;
            modelId: number;
            files: File[];
            collectionName?: string;
            abortControllers?: AbortController[];
            ignoreIndexes?: number[];
        },
        {
            onProgress = (
                progressEvent: AxiosProgressEvent,
                index: number,
            ) => {},
            onSuccess = (uploadedMediaList: MediaEntity[]) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        },
    ): Promise<void> {
        onChangeStatus("loading");

        const uploadedMediaList: MediaEntity[] = [];

        await Promise.all(
            files.map((file, index) => {
                if (ignoreIndexes.includes(index)) {
                    return Promise.resolve();
                }

                return uploadMedia(
                    {
                        modelType,
                        modelId,
                        file,
                        collectionName,
                        abortController: abortControllers
                            ? abortControllers[index]
                            : undefined,
                    },
                    {
                        autoShowDialog: false,
                        onProgress: (progressEvent) => {
                            onProgress(progressEvent, index);
                        },
                        onSuccess: (response) => {
                            uploadedMediaList.push(response.data.result);
                        },
                    },
                );
            }),
        );

        onChangeStatus("success");
        onSuccess(uploadedMediaList);
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

    async function attachMediaToModel(
        {
            mediaIds,
            modelType,
            modelId,
            collectionName = "default",
        }: {
            mediaIds: number[];
            modelType: string;
            modelId: number;
            collectionName: string;
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
            .post(
                `/api/my-store/media/attach`,
                {
                    media_ids: mediaIds,
                    model_type: modelType,
                    model_id: modelId,
                    collection_name: collectionName,
                },
                {
                    headers: {
                        Authorization: token,
                        "X-Selected-Store-ID": selectedStoreId,
                    },
                },
            )
            .then((response) => {
                onChangeStatus("success");
                onSuccess(response);
                if (autoShowDialog) {
                    dialogStore.openSuccessDialog(
                        response.data.meta.message ||
                            `Media berhasil dilampirkan ke ${modelType} dengan ID ${modelId}.`,
                    );
                }
            })
            .catch((error) => {
                console.error("Error attaching media to model:", error);
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            `Terjadi kesalahan saat melampirkan media ke ${modelType} dengan ID ${modelId}.`,
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
        attachMediaToModel,
    };
}

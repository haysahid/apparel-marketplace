import cookieManager from "@/plugins/cookie-manager";
import { useDialogStore } from "@/stores/dialog-store";
import axios, { AxiosProgressEvent, CancelToken } from "axios";

export default function temporaryMediaService() {
    const dialogStore = useDialogStore();
    const token = `Bearer ${cookieManager.getItem("access_token")}`;
    const selectedStoreId = cookieManager.getItem("selected_store_id");

    async function uploadTemporaryMedia(
        {
            file,
            abortController,
        }: { file: File; abortController?: AbortController },
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
            .post(`/api/my-store/temporary-media`, formData, {
                headers: {
                    Authorization: token,
                    "X-Selected-Store-ID": selectedStoreId,
                    "Content-Type": "multipart/form-data",
                },
                signal: abortController ? abortController.signal : undefined,
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
        {
            files,
            abortControllers,
            ignoreIndexes = [],
        }: {
            files: File[];
            abortControllers?: AbortController[];
            ignoreIndexes?: number[];
        },
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
            files.map((file, index) => {
                if (ignoreIndexes.includes(index)) {
                    return Promise.resolve();
                }

                return uploadTemporaryMedia(
                    {
                        file: file,
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
                            uploadedTemporaryMediaList.push(
                                response.data.result,
                            );
                        },
                    },
                );
            }),
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

    async function attachTemporaryMediaToModel(
        {
            temporaryMediaIds,
            modelType,
            modelId,
            collectionName,
        }: {
            temporaryMediaIds: number[];
            modelType: string;
            modelId: number;
            collectionName?: string;
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
                `/api/my-store/temporary-media/attach`,
                {
                    temporary_media_ids: temporaryMediaIds,
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
                            `Media sementara berhasil dilampirkan ke ${modelType} dengan ID ${modelId}.`,
                    );
                }
            })
            .catch((error) => {
                console.error(
                    "Error attaching temporary media to model:",
                    error,
                );
                onChangeStatus("error");
                onError(error);
                if (autoShowDialog) {
                    dialogStore.openErrorDialog(
                        error.response?.data?.meta?.message ||
                            `Terjadi kesalahan saat melampirkan media sementara ke ${modelType} dengan ID ${modelId}.`,
                    );
                }
            });
    }

    return {
        getTemporaryMediaList,
        uploadTemporaryMedia,
        uploadTemporaryMediaBulk,
        attachTemporaryMediaToModel,
    };
}

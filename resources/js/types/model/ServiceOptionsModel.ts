interface ServiceOptionsModel {
    autoShowDialog?: boolean;
    onSuccess?: (response: any) => void;
    onError?: (error: any) => void;
    onChangeStatus?: (status: string) => void;
}

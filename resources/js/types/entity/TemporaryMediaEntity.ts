interface TemporaryMediaEntity {
    id: number;
    store_id: number | null;
    folder: string;
    file_name: string;
    mime_type: string;
    size: number;
    url: string;
    created_at: string;
    updated_at: string;
}

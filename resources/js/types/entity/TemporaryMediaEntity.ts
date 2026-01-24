interface TemporaryMediaEntity extends MediaEntity {
    id: number;
    store_id: number | null;
    folder: string;
    file_name: string;
    mime_type: string;
    size: number;
    original_url: string;
    created_at: string;
    updated_at: string;

    // Additional attributes
    is_temporary: boolean | null;

    // Utilities
    showDeleteDialog?: boolean | false;
}

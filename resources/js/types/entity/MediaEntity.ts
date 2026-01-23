interface MediaEntity {
    id: number;
    collection_name: string;
    name: string;
    file_name: string;
    mime_type: string;
    size: number;
    original_url: string;
    order_column: number;
    created_at: string;
    updated_at: string;

    // Additional attributes
    is_temporary?: boolean;
}

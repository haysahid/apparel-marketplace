interface MediaEntity {
    id: number;
    model_type: string;
    model_id: number;
    uuid: string;
    collection_name: string;
    name: string;
    file_name: string;
    mime_type: string;
    disk: string;
    conversions_disk: string;
    size: number;
    manipulations: any[];
    custom_properties: any[];
    generated_conversions: { [key: string]: boolean };
    responsive_images: any[];
    order_column: number;
    created_at: string;
    updated_at: string;

    original_url: string;
    preview_url?: string;

    // Additional attributes
    is_temporary?: boolean;

    // Utilities
    showDeleteDialog?: boolean | false;
}

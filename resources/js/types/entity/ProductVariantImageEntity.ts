interface ProductVariantImageEntity {
    id?: number | string | null;
    product_variant_id?: number | null;
    product_id?: number | null;
    media_id?: number | null;
    order?: number | null;
    created_at?: string | null;
    updated_at?: string | null;
    deleted_at?: string | null;

    // Additional attributes
    original_url?: string | null;

    // Relationships
    media?: any;
}

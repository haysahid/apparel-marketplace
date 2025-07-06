interface CartItemModel {
    store_id: number;
    product_id: number;
    variant_id: number;
    quantity: number;
    image: string | File | null;
    variant: ProductVariantEntity | null;
    created_at: string;
    updated_at?: string | null;

    // Additional properties for UI state management
    selected: boolean;
    showDeleteConfirmation?: boolean | null;

    // Relationships
    store: StoreEntity | null;
}

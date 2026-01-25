interface TransactionItemEntity {
    id: number;
    store_id: number;
    transaction_id: number;
    variant_id: number;
    quantity: number;
    unit_base_selling_price: number;
    unit_discount_type: string;
    unit_discount: number;
    unit_final_selling_price: number;
    subtotal: number;
    created_at: string;
    updated_at: string;

    // Additional attributes
    image?: string | null;

    // Relationships
    transaction?: TransactionEntity | null;
    variant?: ProductVariantEntity | null;
    store?: StoreEntity | null;
}

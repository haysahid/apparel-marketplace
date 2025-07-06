interface ProductVariantEntity {
    id: number;
    store_id: number;
    product_id: number;
    sku: string;
    barcode: string;
    slug: string;
    motif: string;
    color_id: number;
    size_id: number;
    material: string;
    purchase_price: number;
    base_selling_price: number;
    discount_type: string;
    discount: number;
    final_selling_price: number;
    current_stock_level: number;
    last_stock_update: string | null;
    unit: string;
    disabled_at: string | null;
    created_at: string | null;
    updated_at: string | null;

    // Additional attributes
    name: string;

    // Relationships
    product: ProductEntity | null;
    color: ColorEntity | null;
    size: SizeEntity | null;
    images: ProductVariantImageEntity[];
    transaction_items: TransactionItemEntity[];
}

interface ProductEntity {
    id: number;
    store_id: number;
    name: string;
    slug: string;
    sku_prefix: string;
    description: string;
    discount_type: string;
    discount: number;

    // Additional attributes
    lowest_base_selling_price: number;
    lowest_final_selling_price: number;
    highest_base_selling_price: number;
    highest_final_selling_price: number;

    // Relationships
    store: StoreEntity | null;
    brand: BrandEntity | null;
    categories: CategoryEntity[];
    images: ProductVariantImageEntity[];
    links: ProductLinkEntity[];
    variants: ProductVariantEntity[];
}

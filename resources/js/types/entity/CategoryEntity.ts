interface CategoryEntity {
    id: number;
    store_id: number | null;
    name: string;
    image: string | null;
    created_at: string | null;
    update_at: string | null;

    // Additional attributes
    total_products?: number;
}

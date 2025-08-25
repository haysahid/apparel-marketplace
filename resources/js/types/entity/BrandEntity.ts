interface BrandEntity {
    id: number;
    name: string;
    description: string | null;
    logo: string | null;
    website: string | null;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;

    // Additional Attributes
    products_count: number | null;
    selected: boolean | null;
}

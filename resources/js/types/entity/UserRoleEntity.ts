interface RoleEntity {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    created_at: string | null;
    updated_at: string | null;

    // Relationships
    users?: UserEntity[] | null;

    // Pivot
    pivot?: {
        user_id: number;
        role_id: number;
        store_id: number;
        created_at: string | null;
        updated_at: string | null;
    } | null;
}

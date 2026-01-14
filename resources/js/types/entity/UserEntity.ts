interface StoreRolePair {
    store: StoreEntity | null;
    role: RoleEntity | null;
}

interface UserEntity {
    id: number;
    name: string;
    username: string;
    email: string;
    password?: string | null;
    phone: string | null;
    address: string | null;
    avatar: string | null;
    role_id: number;
    disabled_at: string | null;
    created_at: string | null;
    updated_at: string | null;

    // Relationships
    role: RoleEntity | null;
    stores: StoreEntity[] | null;
    store_roles: RoleEntity[] | null;
    transactions: TransactionEntity[] | null;
    user_points: UserPointEntity[] | null;

    // Additional Attributes
    profile_photo_url: string | null;
    password_confirmation?: string | null;
    store_role_pairs?: StoreRolePair[] | null;

    // Utility Attributes
    showDeleteModal?: boolean;
}

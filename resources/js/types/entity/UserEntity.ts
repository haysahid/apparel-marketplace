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
    store_role_pairs?: StoreRolePair[] | null;
    transactions: TransactionEntity[] | null;
    user_points: UserPointEntity[] | null;

    // Utility Attributes
    showDeleteModal?: boolean;
}

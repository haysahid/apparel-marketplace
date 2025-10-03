export class UserModel implements UserEntity {
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
    showDeleteModal?: boolean;

    constructor(data: UserEntity) {
        // Assign all properties from the raw data object
        Object.assign(this, data);
    }

    // Methods
    getStoreRolePairs(): StoreRolePair[] | null {
        if (this.stores && this.store_roles) {
            return this.stores.map((store) => {
                const role = this.store_roles?.find(
                    (role) =>
                        role.pivot.store_id === store.id &&
                        role.id === role.pivot.role_id
                );
                return {
                    store,
                    role: role || null,
                };
            });
        }
        return null;
    }
}

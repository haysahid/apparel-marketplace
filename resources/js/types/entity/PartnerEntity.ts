interface PartnerEntity {
    id: number;
    store_id: number | null;
    user_id: number | null;
    name: string;
    slug: string;
    description: string | null;
    logo: string | null;
    contact_name: string | null;
    contact_email: string | null;
    contact_phone: string | null;
    address: string | null;
    website: string | null;
    created_at: string | null;
    updated_at: string | null;
    deleted_at?: string | null;

    // Relationships
    store: StoreEntity | null;
    user: UserEntity | null;
    vouchers: VoucherEntity[] | null;
}

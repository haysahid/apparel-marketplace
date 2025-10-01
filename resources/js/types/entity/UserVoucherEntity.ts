interface UserVoucherEntity {
    id: string | null;
    user_id: string | null;
    voucher_id: string | null;
    usage_count: number | null;
    redeemed_at: string | null;
    last_used_at: string | null;
    expired_at: string | null;
    created_at: string | null;
    updated_at: string | null;

    // Relationships
    voucher: VoucherEntity | null;
    user: UserEntity | null;

    // Additional attributes
    is_redeemed: boolean | null;
    status: "active" | "used" | "expired" | null;
}

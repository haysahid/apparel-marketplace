interface VoucherEntity {
    id: string | null;
    store_id: string | null;
    name: string | null;
    code: string | null;
    description: string | null;
    type: string | null;
    amount: number | null;
    min_amount: number | null;
    max_amount: number | null;
    redeem_start_date: string | null;
    redeem_end_date: string | null;
    usage_duration_days: number | null;
    usage_start_date: string | null;
    usage_end_date: string | null;
    usage_limit: number | null;
    required_points: number | null;
    usage_url: string | null;
    is_public: boolean;
    is_internal: boolean;
    partner_id: number | null;
    disabled_at: string | null;
    created_at: string | null;
    updated_at: string | null;

    // Relationships
    store: StoreEntity | null;
    partner: PartnerEntity | null;

    // Utility
    usage_period_type: string | null; // days, range
}

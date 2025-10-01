interface PointRuleEntity {
    id: number;
    store_id: number;
    name: string;
    description: string;
    type:
        | "per_amount_spent"
        | "per_transaction"
        | "on_signup"
        | "on_review"
        | "on_referral"
        | "on_birthday"
        | "on_anniversary"
        | "other"
        | string;
    min_spend: number | null;
    points_earned: number;
    conversion_rate: number | null;
    valid_from: string | null;
    valid_until: string | null;
    disabled_at: string | null;
    created_at: string | null;
    updated_at: string | null;

    // Relationships
    store: StoreEntity | null;
}

interface VoucherEntity {
    id: string;
    storeId: string;
    name: string;
    code: string;
    description: string | null;
    type: string;
    amount: number;
    min_amount: number | null;
    max_amount: number | null;
    start_date: string | null;
    end_date: string | null;
    usage_limit: number | null;
    disabled_at: string | null;
    created_at: string;
    updated_at: string;
}

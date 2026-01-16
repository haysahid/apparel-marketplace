interface MembershipEntity {
    id: number;
    store_id: number;
    group: string;
    name: string;
    alias: string | null;
    level: number;
    slug: string;
    description: string | null;
    item_discount_percentage: number;
    shipping_discount_percentage: number;
    min_purchase_amount: number;
    hex_code_bg: string | null;
    hex_code_text: string | null;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
}

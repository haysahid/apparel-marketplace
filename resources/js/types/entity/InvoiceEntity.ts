interface InvoiceEntity {
    id: number;
    store_id: number;
    transaction_id: number;
    code: string;
    description: string;
    base_amount: number;
    shipping_cost: number;
    tax: number;
    voucher_id: number | null;
    voucher_amount: number;
    amount: number;
    due_date: string;
    paid_at: string | null;
    shipping_estimate: string | null;
    shipped_at: string | null;
    picked_up_at: string | null;
    delivered_at: string | null;
    status: string;
    updated_at: string;
    created_at: string;

    // Relationships
    store: StoreEntity | null;
    transaction: TransactionEntity | null;
    voucher: VoucherEntity | null;
}

interface InvoiceEntity {
    id: number;
    transaction_id: number;
    code: string;
    shipping_cost: number;
    tax: number;
    amount: number;
    due_date: string;
    snap_token?: string;
    updated_at: string;
    created_at: string;

    // Relationships
    transaction: TransactionEntity | null;
}

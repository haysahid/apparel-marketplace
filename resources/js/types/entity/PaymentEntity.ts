interface PaymentEntity {
    id: number;
    transaction_id: number;
    payment_method_id: number;
    amount: number;
    note: string | null;
    reason: string | null;
    image: string | null;
    midtrans_snap_token: string | null;
    midtrans_response: MidtransResponseEntity | null;
    status: string;
    created_at: string | null;
    updated_at: string | null;

    // Relationships
    transaction?: TransactionEntity | null;
    payment_method?: PaymentMethodEntity | null;
}

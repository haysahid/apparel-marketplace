interface PointTransactionEntity {
    id: number;
    user_id: number;
    type: string;
    description: string | null;
    points_amount: number;
    balance_before: number;
    balance_after: number;
    reference_type: string | null;
    transaction_id: number | null;
    voucher_id: number | null;
    admin_id: number | null;
    point_rule_id: number | null;
    created_at: string | null;
    updated_at: string | null;

    // Relationships
    transaction: TransactionEntity | null;
    voucher: VoucherEntity | null;
    admin: UserEntity | null;
    point_rule: PointRuleEntity | null;
}

interface CartGroupModel {
    store_id: number;
    shipping?: ShippingEntity | null;
    store: StoreEntity;
    items: CartItemModel[];
    created_at: string;
    updated_at?: string | null;

    // Additional properties for UI state management
    selected?: boolean;
    showDeleteConfirmation?: boolean | null;

    // Voucher
    voucher?: VoucherEntity | null;
}

interface ShipmentItemEntity {
    id: number;
    shipment_id: number;
    transaction_item_id: number;
    shipped_quantity: number;
    created_at: string | null;
    updated_at: string | null;

    // Relationships
    transaction_item?: TransactionItemEntity;
}

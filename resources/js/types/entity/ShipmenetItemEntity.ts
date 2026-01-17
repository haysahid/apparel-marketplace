interface ShipmentItemEntity {
    id: number | null;
    shipment_id: number | null;
    transaction_item_id: number | null;
    shipped_quantity: number;
    created_at: string | null;
    updated_at: string | null;

    // Relationships
    transaction_item?: TransactionItemEntity;
}

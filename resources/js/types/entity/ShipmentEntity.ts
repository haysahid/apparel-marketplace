interface ShipmentEntity {
    id: number;
    invoice_id: number;
    tracking_number: string;
    courier_name: string;
    weight: number | null;
    shipping_cost: number;
    status: string;
    created_at: string | null;
    updated_at: string | null;

    // Relationships
    items?: ShipmentItemEntity[];
}

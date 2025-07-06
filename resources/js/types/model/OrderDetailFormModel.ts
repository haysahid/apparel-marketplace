interface OrderDetailFormModel {
    payment_method: PaymentMethodEntity | null;
    shipping_method: ShippingMethodEntity | null;
    destination_id: number | null;
    destination: DestinationEntity | null;
    address: string | null;
}

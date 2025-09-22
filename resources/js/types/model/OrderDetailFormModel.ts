interface OrderDetailFormModel {
    payment_method: PaymentMethodEntity | null;
    shipping_method: ShippingMethodEntity | null;
    destination_id: number | null;
    destination: DestinationEntity | null;
    address: string | null;

    // Guest Info
    guest_name?: string | null;
    guest_email?: string | null;
    guest_phone?: string | null;

    // Customer Info
    customer: UserEntity | null;
}

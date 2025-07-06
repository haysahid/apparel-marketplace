interface OrderGroupModel {
    store_id: number;
    store: StoreEntity;
    invoice: InvoiceEntity;
    items: TransactionItemEntity[];
}

export const formatCurrency = (value, options = {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
}) => {
    if (value === null || value === undefined) {
        value = 0;
    }
    return value.toLocaleString("id-ID", options);
}

export const formatNumber = (value, options = {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
}) => {
    if (value === null || value === undefined) {
        value = 0;
    }
    return value.toLocaleString("id-ID", options);
}
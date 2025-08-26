<script setup>
const props = defineProps({
    salesReport: null,
    totals: null,
});
</script>

<template>
    <table class="table-report">
        <thead>
            <tr>
                <th class="w-6">No</th>
                <th class="w-6">Tanggal</th>
                <th class="w-6">Kode</th>
                <th>Pemesan</th>
                <th class="w-6">Item</th>
                <th>Total Penjualan</th>
                <th>Total Diskon</th>
                <th>Margin</th>
            </tr>
        </thead>
        <tbody>
            <tr
                v-for="(transaction, index) in props.salesReport"
                :key="transaction.id"
            >
                <td>{{ index + 1 }}</td>
                <td>{{ transaction.date }}</td>
                <td>{{ transaction.code }}</td>
                <td>{{ transaction.customer }}</td>
                <td class="text-center">
                    {{ transaction.total_items }}
                </td>
                <td class="text-right">
                    {{ $formatCurrency(transaction.total_sales) }}
                </td>
                <td class="text-right">
                    {{ $formatCurrency(transaction.total_discounts) }}
                </td>
                <td class="text-right">
                    {{ $formatCurrency(transaction.margin) }}
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="!text-center">Total</td>
                <td>
                    {{ $formatCurrency(props.totals.total_sales) }}
                </td>
                <td>
                    {{ $formatCurrency(props.totals.total_discounts) }}
                </td>
                <td>
                    {{ $formatCurrency(props.totals.margin) }}
                </td>
            </tr>
        </tfoot>
    </table>
</template>

<script setup>
const props = defineProps({
    purchaseReport: null,
    totals: null,
});
</script>

<template>
    <table class="table-report">
        <thead>
            <tr>
                <th class="w-12">No</th>
                <th class="w-12">Tanggal</th>
                <th class="w-12">Kode</th>
                <th>Pemesan</th>
                <th class="w-12">Item</th>
                <th>Total Penjualan</th>
                <th>Total Diskon</th>
                <th>Margin</th>
            </tr>
        </thead>
        <tbody>
            <tr
                v-for="(transaction, index) in props.purchaseReport"
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
    </table>
</template>

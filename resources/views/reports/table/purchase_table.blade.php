<?php use App\Helpers\AppNumberFormatter; ?>

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
        @foreach ($report as $index => $transaction)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $transaction['date'] }}</td>
                <td>{{ $transaction['code'] }}</td>
                <td>{{ $transaction['customer'] }}</td>
                <td class="text-center">
                    {{ $transaction['total_items'] }}
                </td>
                <td class="text-right">
                    {{ AppNumberFormatter::formatCurrency($transaction['total_sales']) }}
                </td>
                <td class="text-right">
                    {{ AppNumberFormatter::formatCurrency($transaction['total_discounts']) }}
                </td>
                <td class="text-right">
                    {{ AppNumberFormatter::formatCurrency($transaction['margin']) }}
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" class="!text-center">Total</td>
            <td>
                {{ AppNumberFormatter::formatCurrency($totals['total_sales']) }}
            </td>
            <td>
                {{ AppNumberFormatter::formatCurrency($totals['total_discounts']) }}
            </td>
            <td>
                {{ AppNumberFormatter::formatCurrency($totals['margin']) }}
            </td>
        </tr>
    </tfoot>
</table>

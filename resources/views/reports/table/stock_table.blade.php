<?php use App\Helpers\AppNumberFormatter; ?>

<table class="table-report">
    <thead>
        <tr>
            <th class="w-6">No</th>
            <th>Produk</th>
            <th class="w-6">Brand</th>
            <th class="w-6">SKU</th>
            <th class="w-6">Motif</th>
            <th class="w-6">Warna</th>
            <th class="w-6">Ukuran</th>
            <th class="w-6">Stok</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($report as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>

                @if ($index == 0 || $item->product_id !== $report[$index - 1]->product_id)
                    <td class="!whitespace-normal">{{ $item->product_name }}</td>
                @else
                    <td></td>
                @endif

                @if (
                    $index == 0 ||
                        $item->brand_name !== $report[$index - 1]->brand_name ||
                        $item->product_name !== $report[$index - 1]->product_name)
                    <td class="!whitespace-normal">{{ $item->brand_name }}</td>
                @else
                    <td></td>
                @endif

                <td class="!whitespace-normal break-all">{{ wordwrap($item->sku, 20, "\n", true) }}</td>
                <td class="!whitespace-normal">{{ $item->motif }}</td>
                <td class="!whitespace-normal">{{ $item->color_name }}</td>
                <td class="!whitespace-normal text-center">{{ $item->size_name }}</td>
                <td class="text-center">
                    {{ AppNumberFormatter::formatNumber($item->current_stock_level ?? 0) }}
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" class="!text-center">Total</td>
            <td class="!text-center">
                {{ AppNumberFormatter::formatNumber($totals['total_stock'] ?? 0) }}
            </td>
        </tr>
    </tfoot>
</table>

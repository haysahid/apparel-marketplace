<script setup>
const props = defineProps({
    stockReport: null,
    totals: null,
});
</script>

<template>
    <table class="table-report">
        <thead>
            <tr>
                <th class="w-6">No</th>
                <th>Produk</th>
                <th>Brand</th>
                <th class="w-[150px]">SKU</th>
                <th>Motif</th>
                <th>Warna</th>
                <th>Ukuran</th>
                <th class="w-6">Stok</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(item, index) in props.stockReport" :key="index">
                <td>{{ index + 1 }}</td>

                <td
                    v-if="
                        index == 0 ||
                        item.product_id !==
                            props.stockReport[index - 1].product_id
                    "
                    class="!whitespace-normal"
                >
                    {{ item.product_name }}
                </td>
                <td v-else></td>

                <td
                    v-if="
                        index == 0 ||
                        item.brand_name !==
                            props.stockReport[index - 1].brand_name ||
                        item.product_name !==
                            props.stockReport[index - 1].product_name
                    "
                    class="!whitespace-normal"
                >
                    {{ item.brand_name }}
                </td>
                <td v-else></td>

                <td class="!whitespace-normal break-all">{{ item.sku }}</td>
                <td class="!whitespace-normal">{{ item.motif }}</td>
                <td class="!whitespace-normal">{{ item.color_name }}</td>
                <td class="!whitespace-normal text-center">
                    {{ item.size_name }}
                </td>
                <td class="text-center">
                    {{
                        (item.current_stock_level ?? 0).toLocaleString("id-ID")
                    }}
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="!text-center">Total</td>
                <td class="!text-center">
                    {{
                        (props.totals.total_stock ?? 0).toLocaleString("id-ID")
                    }}
                </td>
            </tr>
        </tfoot>
    </table>
</template>

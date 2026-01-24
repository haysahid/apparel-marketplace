<script setup lang="ts">
interface TableRow {
    label: string;
    value: string | number;
}

const props = defineProps({
    rows: {
        type: Array as () => TableRow[],
        required: true,
    },
});
</script>

<template>
    <table class="w-full">
        <tbody
            class="font-semibold [&>tr]:border-b [&>tr]:border-gray-300 [&>tr>td]:p-2 [&>tr:last-child]:border-b-0 [&>tr>td:first-child]:bg-gray-100 [&>tr>td]:text-gray-600 [&>tr>td]:text-sm [&>tr>td:first-child]:font-medium"
        >
            <tr v-for="(row, index) in props.rows" :key="index">
                <td class="w-12 whitespace-nowrap">{{ row.label }}</td>
                <td>{{ row.value }}</td>
            </tr>
        </tbody>
    </table>
</template>

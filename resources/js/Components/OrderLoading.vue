<script setup>
const props = defineProps({
    cardTitle1: {
        type: String,
        default: "Data Pengguna",
    },
    cardTitle2: {
        type: String,
        default: "Ringkasan Transaksi",
    },
});
</script>

<template>
    <div class="flex flex-col w-full gap-5 xl:max-w-sm animate-pulse">
        <div
            class="flex flex-col w-full gap-3 p-4 rounded-2xl outline outline-1 -outline-offset-1 outline-gray-300"
        >
            <h3 class="font-semibold text-gray-800">{{ props.cardTitle1 }}</h3>
            <div class="flex items-center justify-between gap-x-2">
                <div class="w-1/2 h-4 bg-gray-200 rounded-lg"></div>
                <div class="w-1/3 h-4 bg-gray-200 rounded-lg"></div>
            </div>
            <div class="flex items-center justify-between gap-x-2">
                <div class="w-1/3 h-4 bg-gray-200 rounded-lg"></div>
                <div class="w-1/5 h-4 bg-gray-200 rounded-lg"></div>
            </div>
            <div class="flex items-center justify-between gap-x-2">
                <div class="w-2/5 h-4 bg-gray-200 rounded-lg"></div>
                <div class="w-1/5 h-4 bg-gray-200 rounded-lg"></div>
            </div>
        </div>

        <div
            class="flex flex-col w-full gap-3 p-4 rounded-2xl outline outline-1 -outline-offset-1 outline-gray-300"
        >
            <h3 class="font-semibold text-gray-800">{{ props.cardTitle2 }}</h3>
            <template v-for="n in 3" :key="n">
                <div class="flex items-center justify-between gap-x-2">
                    <div class="w-1/2 h-4 bg-gray-200 rounded-lg"></div>
                    <div class="w-1/3 h-4 bg-gray-200 rounded-lg"></div>
                </div>
                <div class="flex items-center justify-between gap-x-2">
                    <div class="w-1/3 h-4 bg-gray-200 rounded-lg"></div>
                    <div class="w-1/5 h-4 bg-gray-200 rounded-lg"></div>
                </div>
                <div class="flex items-center justify-between gap-x-2">
                    <div class="w-2/5 h-4 bg-gray-200 rounded-lg"></div>
                    <div class="w-1/5 h-4 bg-gray-200 rounded-lg"></div>
                </div>
            </template>
        </div>
    </div>
</template>

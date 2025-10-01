<script setup lang="ts">
const props = defineProps({
    voucher: {
        type: Object as () => VoucherEntity,
        required: true,
    },
});
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-2.5">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="26"
                height="26"
                viewBox="0 0 26 26"
                class="fill-gray-500 size-16 shrink-0"
            >
                <path
                    d="M4.33317 4.33325C3.75853 4.33325 3.20743 4.56153 2.80111 4.96785C2.39478 5.37418 2.1665 5.92528 2.1665 6.49992V10.8333C2.74114 10.8333 3.29224 11.0615 3.69857 11.4679C4.1049 11.8742 4.33317 12.4253 4.33317 12.9999C4.33317 13.5746 4.1049 14.1257 3.69857 14.532C3.29224 14.9383 2.74114 15.1666 2.1665 15.1666V19.4999C2.1665 20.0746 2.39478 20.6257 2.80111 21.032C3.20743 21.4383 3.75853 21.6666 4.33317 21.6666H21.6665C22.2411 21.6666 22.7922 21.4383 23.1986 21.032C23.6049 20.6257 23.8332 20.0746 23.8332 19.4999V15.1666C23.2585 15.1666 22.7074 14.9383 22.3011 14.532C21.8948 14.1257 21.6665 13.5746 21.6665 12.9999C21.6665 12.4253 21.8948 11.8742 22.3011 11.4679C22.7074 11.0615 23.2585 10.8333 23.8332 10.8333V6.49992C23.8332 5.92528 23.6049 5.37418 23.1986 4.96785C22.7922 4.56153 22.2411 4.33325 21.6665 4.33325H4.33317ZM16.7915 7.58325L18.4165 9.20825L9.20817 18.4166L7.58317 16.7916L16.7915 7.58325ZM9.544 7.62659C10.6057 7.62659 11.4615 8.48242 11.4615 9.54409C11.4615 10.0526 11.2595 10.5404 10.8999 10.9C10.5403 11.2596 10.0526 11.4616 9.544 11.4616C8.48234 11.4616 7.6265 10.6058 7.6265 9.54409C7.6265 9.03553 7.82853 8.54781 8.18813 8.18821C8.54773 7.82861 9.03545 7.62659 9.544 7.62659ZM16.4557 14.5383C17.5173 14.5383 18.3732 15.3941 18.3732 16.4558C18.3732 16.9643 18.1711 17.452 17.8115 17.8116C17.4519 18.1712 16.9642 18.3733 16.4557 18.3733C15.394 18.3733 14.5382 17.5174 14.5382 16.4558C14.5382 15.9472 14.7402 15.4595 15.0998 15.0999C15.4594 14.7403 15.9471 14.5383 16.4557 14.5383Z"
                />
            </svg>
            <div>
                <div
                    v-if="props.voucher.name?.trim()"
                    class="font-semibold text-gray-900"
                >
                    {{ props.voucher.name }}
                </div>
                <div v-else class="italic font-semibold text-gray-900">
                    Nama Voucher
                </div>

                <div class="text-xs text-gray-600">
                    <span :class="props.voucher.code?.trim() ? '' : 'italic'">{{
                        props.voucher.code?.trim() || "Kode Voucher"
                    }}</span>
                    -
                    <span :class="props.voucher.amount ? '' : 'italic'">
                        {{
                            props.voucher.amount
                                ? props.voucher.type === "percentage"
                                    ? props.voucher.amount + "%"
                                    : $formatCurrency(props.voucher.amount)
                                : "Nilai"
                        }}
                    </span>
                </div>
            </div>
        </div>

        <div>
            <p class="text-xs text-gray-600">Deskripsi</p>
            <p class="mt-1 text-sm text-gray-800">
                {{ props.voucher.description?.trim() || "-" }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-600">Tgl. Mulai</p>
                <p class="mt-1 text-sm text-gray-800">
                    {{
                        $formatDate(props.voucher.redeem_start_date, {
                            dateStyle: "medium",
                        }) || "-"
                    }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-600">Tgl. Berakhir</p>
                <p class="mt-1 text-sm text-gray-800">
                    {{
                        $formatDate(props.voucher.redeem_end_date, {
                            year: "numeric",
                            month: "short",
                            day: "numeric",
                        }) || "-"
                    }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-600">Poin Penukaran</p>
                <p class="mt-1 text-sm text-gray-800">
                    {{
                        props.voucher.required_points
                            ? props.voucher.required_points + " poin"
                            : "0 poin"
                    }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-600">Batas Penggunaan</p>
                <p class="mt-1 text-sm text-gray-800">
                    {{
                        props.voucher.usage_limit
                            ? props.voucher.usage_limit + " kali"
                            : "Tanpa batas"
                    }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-600">Minimal Diskon</p>
                <p class="mt-1 text-sm text-gray-800">
                    {{ $formatCurrency(props.voucher.min_amount || 0) }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-600">Maksimal Diskon</p>
                <p class="mt-1 text-sm text-gray-800">
                    {{
                        props.voucher.max_amount
                            ? $formatCurrency(props.voucher.max_amount)
                            : "Tanpa batas"
                    }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-600">Tgl. Mulai Penggunaan</p>
                <p class="mt-1 text-sm text-gray-800">
                    {{
                        $formatDate(
                            props.voucher.usage_start_date ||
                                props.voucher.redeem_start_date,
                            {
                                dateStyle: "medium",
                            }
                        ) || "-"
                    }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-600">Tgl. Akhir Penggunaan</p>
                <p class="mt-1 text-sm text-gray-800">
                    {{
                        $formatDate(props.voucher.usage_end_date, {
                            dateStyle: "medium",
                        }) || "Tanpa batas"
                    }}
                </p>
            </div>
        </div>

        <div>
            <p class="text-xs text-gray-600">Akses Publik</p>
            <p class="mt-1 text-sm text-gray-800">
                <span class="font-semibold">{{
                    props.voucher.is_public ? "Publik" : "Non-Publik"
                }}</span>
                <span class="italic text-gray-600">
                    -
                    {{
                        props.voucher.is_public
                            ? "Voucher ini dapat dilihat dan digunakan oleh semua pelanggan saat checkout."
                            : "Voucher ini hanya dapat digunakan oleh pelanggan tertentu atau melalui tautan khusus."
                    }}
                </span>
            </p>
        </div>
    </div>
</template>

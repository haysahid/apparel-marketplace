<script setup lang="ts">
import DefaultCard from "@/Components/DefaultCard.vue";
import SummaryCard from "@/Components/SummaryCard.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import { ref } from "vue";
import MyOrderCard from "../Order/MyOrderCard.vue";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import ThreeDotsLoading from "@/Components/ThreeDotsLoading.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { getWhatsAppLink } from "@/plugins/helpers";
import cookieManager from "@/plugins/cookie-manager";

const props = defineProps({
    customer: Object as () => UserEntity,
    count_orders: Number,
    count_active_orders: Number,
    count_completed_orders: Number,
    count_cancelled_orders: Number,
    total_spent: Number,
});

const invoices = ref<PaginationModel<InvoiceEntity>>(null);
const getInvoicesStatus = ref(null);
const invoiceFilter = ref({
    page: 1,
    limit: 5,
});

function getInvoices() {
    getInvoicesStatus.value = "loading";
    axios
        .get("/api/my-store/invoice", {
            params: {
                user_id: props.customer?.id,
                page: invoiceFilter.value.page,
                limit: invoiceFilter.value.limit,
            },
            headers: {
                Authorization: `Bearer ${cookieManager.getItem(
                    "access_token"
                )}`,
            },
        })
        .then((response) => {
            invoices.value = response.data.result;
            getInvoicesStatus.value = "success";
        })
        .catch((error) => {
            console.error("Error fetching invoices:", error);
            getInvoicesStatus.value = "error";
        });
}
getInvoices();

const userVouchers = ref<PaginationModel<UserVoucherEntity>>(null);
const getVouchersStatus = ref(null);
function getUserVouchers() {
    getVouchersStatus.value = "loading";
    axios
        .get(`/api/my-store/customer/${props.customer.id}/voucher`, {
            headers: {
                Authorization: `Bearer ${cookieManager.getItem(
                    "access_token"
                )}`,
            },
        })
        .then((response) => {
            userVouchers.value = response.data.result;
            getVouchersStatus.value = "success";
        })
        .catch((error) => {
            console.error("Error fetching vouchers:", error);
            getVouchersStatus.value = "error";
        });
}
getUserVouchers();
</script>

<template>
    <MyStoreLayout
        title="Detail Pelanggan"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Pelanggan', url: '/my-store/customer', active: false },
            { text: 'Detail Pelanggan', active: true },
        ]"
    >
        <div class="flex flex-col w-full gap-1 sm:gap-2 p-1.5 sm:p-0">
            <!-- Profile -->
            <DefaultCard>
                <div
                    class="flex flex-col items-center w-full gap-3 sm:flex-row"
                >
                    <img
                        v-if="props.customer.avatar"
                        :src="$getImageUrl(props.customer.avatar)"
                        alt="Foto Pelanggan"
                        class="object-contain rounded-full size-[100px] h-fit shrink-0"
                    />
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        width="44"
                        height="44"
                        viewBox="0 0 44 44"
                        class="size-[100px] h-fit fill-gray-400 shrink-0"
                    >
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M40.3333 22.0003C40.3333 32.1258 32.1255 40.3337 22 40.3337C11.8745 40.3337 3.66663 32.1258 3.66663 22.0003C3.66663 11.8748 11.8745 3.66699 22 3.66699C32.1255 3.66699 40.3333 11.8748 40.3333 22.0003ZM27.5 16.5003C27.5 17.959 26.9205 19.358 25.889 20.3894C24.8576 21.4209 23.4586 22.0003 22 22.0003C20.5413 22.0003 19.1423 21.4209 18.1109 20.3894C17.0794 19.358 16.5 17.959 16.5 16.5003C16.5 15.0416 17.0794 13.6427 18.1109 12.6112C19.1423 11.5798 20.5413 11.0003 22 11.0003C23.4586 11.0003 24.8576 11.5798 25.889 12.6112C26.9205 13.6427 27.5 15.0416 27.5 16.5003ZM22 37.5837C25.1465 37.5887 28.2201 36.6366 30.8128 34.8538C31.9201 34.093 32.3931 32.6447 31.7478 31.4658C30.415 29.022 27.665 27.5003 22 27.5003C16.335 27.5003 13.585 29.022 12.2503 31.4658C11.6068 32.6447 12.0798 34.093 13.1871 34.8538C15.7798 36.6366 18.8535 37.5887 22 37.5837Z"
                        />
                    </svg>

                    <div
                        class="flex flex-col items-center justify-center w-full gap-2 sm:items-start"
                    >
                        <p class="font-bold text-gray-900 md:text-lg">
                            {{ props.customer.name }}
                        </p>
                        <div
                            class="flex flex-wrap items-start justify-center w-full text-sm text-gray-600 max-sm:flex-col sm:justify-start gap-x-6 gap-y-1"
                        >
                            <div
                                v-if="props.customer.email"
                                class="flex items-center gap-0.5"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    class="inline-block mr-1 size-5 fill-gray-400"
                                >
                                    <path
                                        d="M4 20C3.45 20 2.97933 19.8043 2.588 19.413C2.19667 19.0217 2.00067 18.5507 2 18V6C2 5.45 2.196 4.97933 2.588 4.588C2.98 4.19667 3.45067 4.00067 4 4H20C20.55 4 21.021 4.196 21.413 4.588C21.805 4.98 22.0007 5.45067 22 6V18C22 18.55 21.8043 19.021 21.413 19.413C21.0217 19.805 20.5507 20.0007 20 20H4ZM12 12.825C12.0833 12.825 12.171 12.8123 12.263 12.787C12.355 12.7617 12.4423 12.7243 12.525 12.675L19.6 8.25C19.7333 8.16667 19.8333 8.06267 19.9 7.938C19.9667 7.81333 20 7.67567 20 7.525C20 7.19167 19.8583 6.94167 19.575 6.775C19.2917 6.60833 19 6.61667 18.7 6.8L12 11L5.3 6.8C5 6.61667 4.70833 6.61267 4.425 6.788C4.14167 6.96333 4 7.209 4 7.525C4 7.69167 4.03333 7.83767 4.1 7.963C4.16667 8.08833 4.26667 8.184 4.4 8.25L11.475 12.675C11.5583 12.725 11.646 12.7627 11.738 12.788C11.83 12.8133 11.9173 12.8257 12 12.825Z"
                                    />
                                </svg>
                                <span>
                                    {{ props.customer.email }}
                                </span>
                            </div>
                            <div
                                v-if="props.customer.phone"
                                class="flex items-center gap-0.5"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    class="inline-block mr-1 size-5 fill-gray-400"
                                >
                                    <path
                                        d="M20.4868 17.14L16.4218 13.444C16.2298 13.2691 15.9772 13.1758 15.7176 13.1838C15.458 13.1919 15.2117 13.3006 15.0308 13.487L12.6378 15.948C12.0618 15.838 10.9038 15.477 9.71179 14.288C8.51979 13.095 8.15879 11.934 8.05179 11.362L10.5108 8.968C10.6972 8.78712 10.8059 8.54082 10.814 8.2812C10.822 8.02159 10.7287 7.76904 10.5538 7.57699L6.85879 3.51299C6.68384 3.32035 6.44067 3.2035 6.18095 3.18725C5.92122 3.17101 5.66539 3.25665 5.46779 3.42599L3.29779 5.28699C3.1249 5.46051 3.02171 5.69145 3.00779 5.93599C2.99279 6.18599 2.70679 12.108 7.29879 16.702C11.3048 20.707 16.3228 21 17.7048 21C17.9068 21 18.0308 20.994 18.0638 20.992C18.3081 20.9776 18.5387 20.874 18.7118 20.701L20.5718 18.53C20.7418 18.333 20.8281 18.0774 20.8122 17.8177C20.7963 17.558 20.6795 17.3148 20.4868 17.14Z"
                                    />
                                </svg>
                                <span>
                                    {{ props.customer.phone }}
                                </span>
                            </div>
                            <div
                                v-if="props.customer.store_roles?.length"
                                class="flex items-center gap-0.5"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    class="inline-block mr-1 size-5 fill-gray-400"
                                >
                                    <path
                                        d="M20 4H4C2.897 4 2 4.897 2 6V18C2 19.103 2.897 20 4 20H20C21.103 20 22 19.103 22 18V6C22 4.897 21.103 4 20 4ZM8.715 8C9.866 8 10.715 8.849 10.715 10C10.715 11.151 9.866 12 8.715 12C7.564 12 6.715 11.151 6.715 10C6.715 8.849 7.563 8 8.715 8ZM12.43 16H5V15.535C5 14.162 6.676 12.75 8.715 12.75C10.754 12.75 12.43 14.162 12.43 15.535V16ZM19 15H15V13H19V15ZM19 11H14V9H19V11Z"
                                    />
                                </svg>
                                <span>
                                    {{
                                        props.customer.store_roles
                                            ?.map((role) => role.name)
                                            .join(", ")
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <Link
                        v-if="props.customer.phone"
                        :href="
                            getWhatsAppLink(
                                props.customer.phone,
                                'Halo, saya ingin bertanya tentang produk Anda.'
                            )
                        "
                        target="_blank"
                        class="mt-2"
                    >
                        <PrimaryButton
                            @click="$event.stopPropagation()"
                            class="px-3 py-1 text-sm text-white !bg-green-600 rounded-md hover:!bg-green-700 focus:!ring-green-600 focus:!ring-offset-2 focus:!ring-2 focus:!bg-green-700"
                        >
                            <template #prefix>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="25"
                                    viewBox="0 0 24 25"
                                    class="fill-white size-5"
                                >
                                    <path
                                        d="M19.0498 5.60488C18.1329 4.67898 17.0408 3.94484 15.8373 3.44524C14.6338 2.94564 13.3429 2.69056 12.0398 2.69488C6.5798 2.69488 2.1298 7.14488 2.1298 12.6049C2.1298 14.3549 2.5898 16.0549 3.4498 17.5549L2.0498 22.6949L7.2998 21.3149C8.7498 22.1049 10.3798 22.5249 12.0398 22.5249C17.4998 22.5249 21.9498 18.0749 21.9498 12.6149C21.9498 9.96488 20.9198 7.47488 19.0498 5.60488ZM12.0398 20.8449C10.5598 20.8449 9.1098 20.4449 7.8398 19.6949L7.5398 19.5149L4.4198 20.3349L5.2498 17.2949L5.0498 16.9849C4.22735 15.6719 3.79073 14.1542 3.7898 12.6049C3.7898 8.06488 7.4898 4.36488 12.0298 4.36488C14.2298 4.36488 16.2998 5.22488 17.8498 6.78488C18.6174 7.54874 19.2257 8.45742 19.6394 9.45821C20.0531 10.459 20.264 11.532 20.2598 12.6149C20.2798 17.1549 16.5798 20.8449 12.0398 20.8449ZM16.5598 14.6849C16.3098 14.5649 15.0898 13.9649 14.8698 13.8749C14.6398 13.7949 14.4798 13.7549 14.3098 13.9949C14.1398 14.2449 13.6698 14.8049 13.5298 14.9649C13.3898 15.1349 13.2398 15.1549 12.9898 15.0249C12.7398 14.9049 11.9398 14.6349 10.9998 13.7949C10.2598 13.1349 9.7698 12.3249 9.6198 12.0749C9.4798 11.8249 9.5998 11.6949 9.7298 11.5649C9.8398 11.4549 9.9798 11.2749 10.0998 11.1349C10.2198 10.9949 10.2698 10.8849 10.3498 10.7249C10.4298 10.5549 10.3898 10.4149 10.3298 10.2949C10.2698 10.1749 9.7698 8.95488 9.5698 8.45488C9.3698 7.97488 9.1598 8.03488 9.0098 8.02488H8.5298C8.3598 8.02488 8.0998 8.08488 7.8698 8.33488C7.6498 8.58488 7.0098 9.18488 7.0098 10.4049C7.0098 11.6249 7.89981 12.8049 8.0198 12.9649C8.1398 13.1349 9.7698 15.6349 12.2498 16.7049C12.8398 16.9649 13.2998 17.1149 13.6598 17.2249C14.2498 17.4149 14.7898 17.3849 15.2198 17.3249C15.6998 17.2549 16.6898 16.7249 16.8898 16.1449C17.0998 15.5649 17.0998 15.0749 17.0298 14.9649C16.9598 14.8549 16.8098 14.8049 16.5598 14.6849Z"
                                    />
                                </svg>
                            </template>
                            Hubungi
                        </PrimaryButton>
                    </Link>
                </div>
            </DefaultCard>

            <!-- Summary -->
            <div class="grid w-full grid-cols-2 gap-1 lg:grid-cols-4 sm:gap-2">
                <!-- <SummaryCard
                    title="Pesanan"
                    :value="$formatNumber(props.count_orders)"
                /> -->
                <SummaryCard
                    title="Pesanan Aktif"
                    :value="$formatNumber(props.count_active_orders)"
                />
                <SummaryCard
                    title="Pesanan Selesai"
                    :value="$formatNumber(props.count_completed_orders)"
                />
                <SummaryCard
                    title="Pesanan Dibatalkan"
                    :value="$formatNumber(props.count_cancelled_orders)"
                />
                <SummaryCard
                    title="Pengeluaran"
                    :value="$formatCurrency(props.total_spent)"
                />
            </div>

            <div class="flex flex-col w-full gap-1 lg:flex-row sm:gap-2">
                <!-- Order History -->
                <DefaultCard class="w-full">
                    <h3 class="font-semibold text-gray-900">Riwayat Pesanan</h3>
                    <div class="w-full mt-2.5">
                        <div
                            v-if="invoices && invoices.data.length"
                            class="flex flex-col w-full gap-2"
                        >
                            <MyOrderCard
                                v-for="invoice in invoices.data"
                                :key="invoice.id"
                                :invoice="invoice"
                            >
                                <template v-if="invoice.voucher" #extra-info>
                                    <div class="flex items-center gap-0.5">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            class="inline-block size-3.5 mr-1 fill-gray-400"
                                        >
                                            <path
                                                d="M4.33317 4.33325C3.75853 4.33325 3.20743 4.56153 2.80111 4.96785C2.39478 5.37418 2.1665 5.92528 2.1665 6.49992V10.8333C2.74114 10.8333 3.29224 11.0615 3.69857 11.4679C4.1049 11.8742 4.33317 12.4253 4.33317 12.9999C4.33317 13.5746 4.1049 14.1257 3.69857 14.532C3.29224 14.9383 2.74114 15.1666 2.1665 15.1666V19.4999C2.1665 20.0746 2.39478 20.6257 2.80111 21.032C3.20743 21.4383 3.75853 21.6666 4.33317 21.6666H21.6665C22.2411 21.6666 22.7922 21.4383 23.1986 21.032C23.6049 20.6257 23.8332 20.0746 23.8332 19.4999V15.1666C23.2585 15.1666 22.7074 14.9383 22.3011 14.532C21.8948 14.1257 21.6665 13.5746 21.6665 12.9999C21.6665 12.4253 21.8948 11.8742 22.3011 11.4679C22.7074 11.0615 23.2585 10.8333 23.8332 10.8333V6.49992C23.8332 5.92528 23.6049 5.37418 23.1986 4.96785C22.7922 4.56153 22.2411 4.33325 21.6665 4.33325H4.33317ZM16.7915 7.58325L18.4165 9.20825L9.20817 18.4166L7.58317 16.7916L16.7915 7.58325ZM9.544 7.62659C10.6057 7.62659 11.4615 8.48242 11.4615 9.54409C11.4615 10.0526 11.2595 10.5404 10.8999 10.9C10.5403 11.2596 10.0526 11.4616 9.544 11.4616C8.48234 11.4616 7.6265 10.6058 7.6265 9.54409C7.6265 9.03553 7.82853 8.54781 8.18813 8.18821C8.54773 7.82861 9.03545 7.62659 9.544 7.62659ZM16.4557 14.5383C17.5173 14.5383 18.3732 15.3941 18.3732 16.4558C18.3732 16.9643 18.1711 17.452 17.8115 17.8116C17.4519 18.1712 16.9642 18.3733 16.4557 18.3733C15.394 18.3733 14.5382 17.5174 14.5382 16.4558C14.5382 15.9472 14.7402 15.4595 15.0998 15.0999C15.4594 14.7403 15.9471 14.5383 16.4557 14.5383Z"
                                            />
                                        </svg>
                                        <span>
                                            {{ invoice.voucher?.code }}
                                        </span>
                                    </div>
                                </template>
                            </MyOrderCard>
                        </div>
                        <div
                            v-else
                            class="flex items-center justify-center h-[10vh] mb-6"
                        >
                            <ThreeDotsLoading
                                v-if="getInvoicesStatus === 'loading'"
                            />
                            <p v-else class="text-sm text-center text-gray-500">
                                Data tidak ditemukan.
                            </p>
                        </div>
                    </div>
                    <div
                        v-if="invoices?.total > 0"
                        class="flex flex-col gap-2 mt-4"
                    >
                        <p class="text-xs text-gray-500 sm:text-sm">
                            Menampilkan {{ invoices.from }} -
                            {{ invoices.to }} dari {{ invoices.total }} item
                        </p>
                        <DefaultPagination
                            :links="invoices.links"
                            :isApi="true"
                            @change="
                                (page) => {
                                    invoiceFilter.page = page;
                                    getInvoices();
                                }
                            "
                        />
                    </div>
                </DefaultCard>

                <!-- Voucher -->
                <DefaultCard class="w-full h-fit">
                    <h3 class="font-semibold text-gray-900">Voucher</h3>
                    <div class="w-full mt-2.5">
                        <div
                            v-if="userVouchers && userVouchers.data.length"
                            class="flex flex-col w-full gap-2"
                        >
                            <div
                                v-for="userVoucher in userVouchers.data"
                                :key="userVoucher.id"
                                class="flex items-center gap-2.5 p-3 border border-gray-200 rounded-lg"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="26"
                                    height="26"
                                    viewBox="0 0 26 26"
                                    class="fill-gray-500 size-8 shrink-0"
                                >
                                    <path
                                        d="M4.33317 4.33325C3.75853 4.33325 3.20743 4.56153 2.80111 4.96785C2.39478 5.37418 2.1665 5.92528 2.1665 6.49992V10.8333C2.74114 10.8333 3.29224 11.0615 3.69857 11.4679C4.1049 11.8742 4.33317 12.4253 4.33317 12.9999C4.33317 13.5746 4.1049 14.1257 3.69857 14.532C3.29224 14.9383 2.74114 15.1666 2.1665 15.1666V19.4999C2.1665 20.0746 2.39478 20.6257 2.80111 21.032C3.20743 21.4383 3.75853 21.6666 4.33317 21.6666H21.6665C22.2411 21.6666 22.7922 21.4383 23.1986 21.032C23.6049 20.6257 23.8332 20.0746 23.8332 19.4999V15.1666C23.2585 15.1666 22.7074 14.9383 22.3011 14.532C21.8948 14.1257 21.6665 13.5746 21.6665 12.9999C21.6665 12.4253 21.8948 11.8742 22.3011 11.4679C22.7074 11.0615 23.2585 10.8333 23.8332 10.8333V6.49992C23.8332 5.92528 23.6049 5.37418 23.1986 4.96785C22.7922 4.56153 22.2411 4.33325 21.6665 4.33325H4.33317ZM16.7915 7.58325L18.4165 9.20825L9.20817 18.4166L7.58317 16.7916L16.7915 7.58325ZM9.544 7.62659C10.6057 7.62659 11.4615 8.48242 11.4615 9.54409C11.4615 10.0526 11.2595 10.5404 10.8999 10.9C10.5403 11.2596 10.0526 11.4616 9.544 11.4616C8.48234 11.4616 7.6265 10.6058 7.6265 9.54409C7.6265 9.03553 7.82853 8.54781 8.18813 8.18821C8.54773 7.82861 9.03545 7.62659 9.544 7.62659ZM16.4557 14.5383C17.5173 14.5383 18.3732 15.3941 18.3732 16.4558C18.3732 16.9643 18.1711 17.452 17.8115 17.8116C17.4519 18.1712 16.9642 18.3733 16.4557 18.3733C15.394 18.3733 14.5382 17.5174 14.5382 16.4558C14.5382 15.9472 14.7402 15.4595 15.0998 15.0999C15.4594 14.7403 15.9471 14.5383 16.4557 14.5383Z"
                                    />
                                </svg>
                                <div class="flex flex-col w-full gap-1">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <p
                                            class="text-sm font-semibold text-gray-900"
                                        >
                                            {{ userVoucher.voucher.name }}
                                        </p>
                                        <div
                                            class="flex items-center justify-center"
                                        >
                                            <span
                                                v-if="
                                                    userVoucher.status ===
                                                    'active'
                                                "
                                                class="px-1.5 py-0.5 text-xs font-medium text-green-800 bg-green-100 rounded-md"
                                                >Aktif</span
                                            >
                                            <span
                                                v-else-if="
                                                    userVoucher.status ===
                                                    'used'
                                                "
                                                class="px-1.5 py-0.5 text-xs font-medium text-blue-800 bg-blue-100 rounded-md"
                                                >Terpakai</span
                                            >
                                            <span
                                                v-else-if="
                                                    userVoucher.status ===
                                                    'expired'
                                                "
                                                class="px-1.5 py-0.5 text-xs font-medium text-red-800 bg-red-100 rounded-md"
                                                >Kadaluarsa</span
                                            >
                                            <span
                                                v-else
                                                class="px-1.5 py-0.5 text-xs font-medium text-gray-800 bg-gray-100 rounded-md"
                                                >Tidak Aktif</span
                                            >
                                        </div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <p class="text-xs text-gray-600">
                                            {{ userVoucher.voucher.code }}
                                        </p>
                                        <p
                                            class="text-xs text-gray-600 text-nowrap"
                                        >
                                            {{ userVoucher.usage_count }}/{{
                                                userVoucher.voucher
                                                    .usage_limit ?? "∞"
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex items-center justify-center h-[10vh] mb-6"
                        >
                            <ThreeDotsLoading
                                v-if="getVouchersStatus === 'loading'"
                            />
                            <p v-else class="text-sm text-center text-gray-500">
                                Data tidak ditemukan.
                            </p>
                        </div>
                    </div>
                </DefaultCard>
            </div>
        </div>
    </MyStoreLayout>
</template>

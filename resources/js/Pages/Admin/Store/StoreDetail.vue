<script setup lang="ts">
import DefaultCard from "@/Components/DefaultCard.vue";
import SummaryCard from "@/Components/SummaryCard.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { onMounted, ref } from "vue";
// import MyOrderCard from "../Order/MyOrderCard.vue";
import DefaultPagination from "@/Components/DefaultPagination.vue";
import ThreeDotsLoading from "@/Components/ThreeDotsLoading.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { getWhatsAppLink } from "@/plugins/helpers";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import cookieManager from "@/plugins/cookie-manager";
import SuccessDialog from "@/Components/SuccessDialog.vue";
import CustomPageProps from "@/types/model/CustomPageProps";
import BaseStoreProfile from "@/Components/BaseStoreProfile.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import MyOrderCard from "@/Pages/MyStore/Order/MyOrderCard.vue";
import storeService from "@/services/admin/store-service";
import WhatsAppButton from "@/Components/WhatsAppButton.vue";
import StoreUserList from "@/Pages/MyStore/Store/StoreUserList.vue";
import { useDialogStore } from "@/stores/dialog-store";

const props = defineProps({
    store: Object as () => StoreEntity,
    count_products: Number,
    count_orders: Number,
    count_revenue: Number,
    roles: Array as () => RoleEntity[],
});

const store = ref<StoreEntity>(props.store);
const userRolePairs = ref<UserRolePair[]>(props.store.user_role_pairs);

const invoices = ref<PaginationModel<InvoiceEntity>>(null);
const getInvoicesStatus = ref(null);
const invoiceFilter = ref({
    page: 1,
    limit: 5,
});

function getInvoices() {
    getInvoicesStatus.value = "loading";
    axios
        .get(`/api/admin/store/${props.store.id}/invoice`, {
            params: {
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

const page = usePage<CustomPageProps>();

function reloadUserRolePairs() {
    router.reload({
        onSuccess: (page) => {
            const newPage = page as unknown as CustomPageProps;
            if (newPage.props.store) {
                userRolePairs.value = newPage.props.store.user_role_pairs;
            }
        },
    });
}

const dialogStore = useDialogStore();

onMounted(() => {
    if (page.props?.flash?.success) {
        dialogStore.openSuccessDialog(page.props.flash.success);
    }
});
</script>

<template>
    <AdminLayout
        title="Detail Toko"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Toko', url: '/admin/store', active: false },
            { text: props.store.name, active: true },
        ]"
    >
        <div class="flex flex-col w-full gap-1 p-1.5 sm:gap-2 sm:p-0">
            <!-- Profile -->
            <BaseStoreProfile
                :store="store"
                @editLogo="
                    (file) => {
                        if (props.store.logo) {
                            storeService().updateStoreLogo(
                                {
                                    storeId: props.store.id,
                                    file,
                                },
                                {
                                    onSuccess: (response) => {
                                        store.logo =
                                            response.data.result.logo_url;
                                    },
                                }
                            );
                        } else {
                            storeService().addStoreLogo(
                                {
                                    storeId: props.store.id,
                                    file,
                                },
                                {
                                    onSuccess: (response) => {
                                        store.logo =
                                            response.data.result.logo_url;
                                    },
                                }
                            );
                        }
                    }
                "
                @editBanner="
                    (file) => {
                        if (props.store.banner) {
                            storeService().updateStoreBanner(
                                {
                                    storeId: props.store.id,
                                    file,
                                },
                                {
                                    onSuccess: (response) => {
                                        store.banner =
                                            response.data.result.banner_url;
                                    },
                                }
                            );
                        } else {
                            storeService().addStoreBanner(
                                {
                                    storeId: props.store.id,
                                    file,
                                },
                                {
                                    onSuccess: (response) => {
                                        store.banner =
                                            response.data.result.banner_url;
                                    },
                                }
                            );
                        }
                    }
                "
            >
                <template #actions>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="
                                route('admin.store.edit', {
                                    store: props.store.id,
                                })
                            "
                        >
                            <SecondaryButton
                                @click="$event.stopPropagation()"
                                class="px-3 py-1"
                            >
                                Ubah
                            </SecondaryButton>
                        </Link>

                        <Link
                            v-if="props.store.phone"
                            :href="
                                getWhatsAppLink(
                                    props.store.phone,
                                    'Halo, saya ingin bertanya mengenai toko Anda.'
                                )
                            "
                            target="_blank"
                        >
                            <WhatsAppButton @click="$event.stopPropagation()">
                                Hubungi
                            </WhatsAppButton>
                        </Link>
                    </div>
                </template>
            </BaseStoreProfile>

            <!-- Summary -->
            <div class="flex items-center w-full gap-1 sm:gap-2">
                <SummaryCard
                    title="Total Produk"
                    :value="$formatNumber(props.count_products)"
                />
                <SummaryCard
                    title="Total Pesanan"
                    :value="$formatNumber(props.count_orders)"
                />
                <SummaryCard
                    title="Total Pendapatan"
                    :value="$formatCurrency(props.count_revenue)"
                />
            </div>

            <div class="flex flex-col w-full gap-1 lg:flex-row sm:gap-2">
                <!-- Users -->
                <!-- Users -->
                <StoreUserList
                    :storeId="
                        route().current().startsWith('admin.')
                            ? props.store.id
                            : null
                    "
                    :userRolePairs="userRolePairs"
                    :roles="props.roles"
                    @reload="reloadUserRolePairs()"
                />

                <!-- Order History -->
                <DefaultCard class="w-full h-fit">
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
            </div>
        </div>
    </AdminLayout>
</template>

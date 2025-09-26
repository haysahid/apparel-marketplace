<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import LandingSection from "@/Components/LandingSection.vue";
import formatDate from "@/plugins/date-formatter";
import OrderGroup from "./OrderGroup.vue";
import OrderSummaryCard from "./OrderSummaryCard.vue";
import DetailRow from "@/Components/DetailRow.vue";
import InfoHint from "@/Components/InfoHint.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import OrderLoading from "@/Components/OrderLoading.vue";

const props = defineProps({
    transaction: {
        type: Object as () => TransactionEntity,
        required: true,
    },
    groups: {
        type: Array as () => OrderGroupModel[],
        required: true,
    },
    showTracking: {
        type: Boolean,
        default: true,
    },
    isShowingFromMyStore: {
        type: Boolean,
        default: false,
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
});

const orderCreated = (date: string | null, status: boolean) => {
    return {
        date: date,
        title: "Pesanan Dibuat",
        done: status,
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path d="M15 4H6V20H18V7H15V4ZM6 2H16L20 6V20C20 20.5304 19.7893 21.0391 19.4142 21.4142C19.0391 21.7893 18.5304 22 18 22H6C5.46957 22 4.96086 21.7893 4.58579 21.4142C4.21071 21.0391 4 20.5304 4 20V4C4 3.46957 4.21071 2.96086 4.58579 2.58579C4.96086 2.21071 5.46957 2 6 2ZM8 11H16V13H8V11ZM8 15H16V17H8V15Z" />
            </svg>`,
    };
};

const paymentReceived = (date: string | null, status: boolean) => {
    return {
        date: date,
        title: "Pembayaran Diterima",
        done: status,
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path d="M20 8H4V6H20M20 18H4V12H20M20 4H4C2.89 4 2 4.89 2 6V18C2 18.5304 2.21071 19.0391 2.58579 19.4142C2.96086 19.7893 3.46957 20 4 20H20C20.5304 20 21.0391 19.7893 21.4142 19.4142C21.7893 19.0391 22 18.5304 22 18V6C22 5.46957 21.7893 4.96086 21.4142 4.58579C21.0391 4.21071 20.5304 4 20 4Z"/>
            </svg>`,
    };
};

const orderShipped = (date: string | null, status: boolean) => {
    return {
        date: date,
        title: "Pesanan Dikirim",
        done: status,
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path d="M18 18.5C18.83 18.5 19.5 17.83 19.5 17C19.5 16.17 18.83 15.5 18 15.5C17.17 15.5 16.5 16.17 16.5 17C16.5 17.83 17.17 18.5 18 18.5ZM19.5 9.5H17V12H21.46L19.5 9.5ZM6 18.5C6.83 18.5 7.5 17.83 7.5 17C7.5 16.17 6.83 15.5 6 15.5C5.17 15.5 4.5 16.17 4.5 17C4.5 17.83 5.17 18.5 6 18.5ZM20 8L23 12V17H21C21 18.66 19.66 20 18 20C16.34 20 15 18.66 15 17H9C9 18.66 7.66 20 6 20C4.34 20 3 18.66 3 17H1V6C1 4.89 1.89 4 3 4H17V8H20ZM3 6V15H3.76C4.31 14.39 5.11 14 6 14C6.89 14 7.69 14.39 8.24 15H15V6H3ZM10 7L13.5 10.5L10 14V11.5H5V9.5H10V7Z"/>
            </svg>`,
    };
};

const orderPickedUp = (date: string | null, status: boolean) => {
    return {
        date: date,
        title: "Ambil Pesanan",
        done: status,
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path d="M22 7.82001C22.006 7.75682 22.006 7.6932 22 7.63001L20 2.63001C19.9219 2.43237 19.7828 2.26475 19.603 2.15147C19.4232 2.03819 19.212 1.98514 19 2.00001H5C4.79972 1.99982 4.604 2.05977 4.43818 2.17209C4.27237 2.28442 4.1441 2.44395 4.07 2.63001L2.07 7.63001C2.06397 7.6932 2.06397 7.75682 2.07 7.82001C2.0371 7.87584 2.01346 7.93663 2 8.00001C2.01113 8.69125 2.20123 9.36781 2.55174 9.96369C2.90226 10.5596 3.40124 11.0544 4 11.4V21C4 21.2652 4.10536 21.5196 4.29289 21.7071C4.48043 21.8947 4.73478 22 5 22H19C19.2652 22 19.5196 21.8947 19.7071 21.7071C19.8946 21.5196 20 21.2652 20 21V11.44C20.6046 11.091 21.1072 10.5898 21.4581 9.98635C21.809 9.38287 21.9958 8.69807 22 8.00001C22.0091 7.94035 22.0091 7.87967 22 7.82001ZM13 20H11V16H13V20ZM18 20H15V15C15 14.7348 14.8946 14.4804 14.7071 14.2929C14.5196 14.1054 14.2652 14 14 14H10C9.73478 14 9.48043 14.1054 9.29289 14.2929C9.10536 14.4804 9 14.7348 9 15V20H6V12C6.56947 11.9968 7.13169 11.872 7.64905 11.634C8.16642 11.3961 8.627 11.0503 9 10.62C9.37537 11.0456 9.83701 11.3865 10.3542 11.62C10.8715 11.8535 11.4325 11.9743 12 11.9743C12.5675 11.9743 13.1285 11.8535 13.6458 11.62C14.163 11.3865 14.6246 11.0456 15 10.62C15.373 11.0503 15.8336 11.3961 16.3509 11.634C16.8683 11.872 17.4305 11.9968 18 12V20ZM18 10C17.4696 10 16.9609 9.7893 16.5858 9.41423C16.2107 9.03915 16 8.53044 16 8.00001C16 7.7348 15.8946 7.48044 15.7071 7.29291C15.5196 7.10537 15.2652 7.00001 15 7.00001C14.7348 7.00001 14.4804 7.10537 14.2929 7.29291C14.1054 7.48044 14 7.7348 14 8.00001C14 8.53044 13.7893 9.03915 13.4142 9.41423C13.0391 9.7893 12.5304 10 12 10C11.4696 10 10.9609 9.7893 10.5858 9.41423C10.2107 9.03915 10 8.53044 10 8.00001C10 7.7348 9.89464 7.48044 9.70711 7.29291C9.51957 7.10537 9.26522 7.00001 9 7.00001C8.73478 7.00001 8.48043 7.10537 8.29289 7.29291C8.10536 7.48044 8 7.7348 8 8.00001C8.00985 8.26266 7.96787 8.52467 7.87646 8.77109C7.78505 9.01751 7.646 9.24351 7.46725 9.43619C7.28849 9.62887 7.07354 9.78446 6.83466 9.89407C6.59578 10.0037 6.33764 10.0652 6.075 10.075C5.54457 10.0949 5.02796 9.90327 4.63882 9.54226C4.44614 9.36351 4.29055 9.14855 4.18094 8.90967C4.07133 8.67079 4.00985 8.41266 4 8.15001L5.68 4.00001H18.32L20 8.15001C19.9621 8.65403 19.7348 9.125 19.3637 9.46822C18.9927 9.81143 18.5054 10.0014 18 10Z" />
            </svg>`,
    };
};

const orderDelivered = (date: string | null, status: boolean) => {
    return {
        date: date,
        title: "Pesanan Diterima",
        done: status,
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path d="M23.5 17L18.5 22L15 18.5L16.5 17L18.5 19L22 15.5L23.5 17ZM6 2C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 21.11 4.89 22 6 22H13.81C13.45 21.38 13.2 20.7 13.08 20H6V4H13V9H18V13.08C18.33 13.03 18.67 13 19 13C19.34 13 19.67 13.03 20 13.08V8L14 2M8 12V14H16V12M8 16V18H13V16H8Z"/>
            </svg>`,
    };
};

const histories = ref([
    orderCreated(formatDate(props.transaction?.created_at), true),
]);

const status = props.transaction.status;
const shippingEstimate = props.transaction.shipping_estimate;

const dayShippingEstimate = computed(() => {
    if (!shippingEstimate) return 0;
    return shippingEstimate.replace(/[^0-9]/g, "");
});

function buildHistories() {
    const {
        payment_method,
        shipping_method,
        paid_at,
        shipped_at,
        picked_up_at,
        delivered_at,
        updated_at,
    } = props.transaction;
    const paymentSlug = payment_method?.slug;
    const shippingSlug = shipping_method?.slug;

    function getDateOrFallback(date, fallback) {
        return formatDate(date ?? fallback);
    }

    // Helper for estimate
    const estimateText = `Estimasi ${dayShippingEstimate.value} hari`;

    // Payment Transfer
    if (paymentSlug === "transfer") {
        if (shippingSlug === "courier") {
            if (status === "pending") {
                return [
                    orderCreated(
                        formatDate(props.transaction?.created_at),
                        true
                    ),
                    paymentReceived("Segera", false),
                    orderShipped("Segera", false),
                    orderDelivered(estimateText, false),
                ];
            }
            if (status === "paid") {
                return [
                    orderCreated(
                        formatDate(props.transaction?.created_at),
                        true
                    ),
                    paymentReceived(
                        getDateOrFallback(paid_at, updated_at),
                        true
                    ),
                    orderShipped("Hari ini", false),
                    orderDelivered(estimateText, false),
                ];
            }
            if (status === "processing") {
                return [
                    orderCreated(
                        formatDate(props.transaction?.created_at),
                        true
                    ),
                    paymentReceived(
                        getDateOrFallback(paid_at, updated_at),
                        true
                    ),
                    orderShipped(
                        getDateOrFallback(shipped_at, updated_at),
                        true
                    ),
                    orderDelivered(estimateText, false),
                ];
            }
            if (status === "completed") {
                return [
                    orderCreated(
                        formatDate(props.transaction?.created_at),
                        true
                    ),
                    paymentReceived(
                        getDateOrFallback(paid_at, updated_at),
                        true
                    ),
                    orderShipped(
                        getDateOrFallback(shipped_at, updated_at),
                        true
                    ),
                    orderDelivered(
                        getDateOrFallback(delivered_at, updated_at),
                        true
                    ),
                ];
            }
        } else if (shippingSlug === "pickup") {
            if (status === "pending") {
                return [
                    orderCreated(
                        formatDate(props.transaction?.created_at),
                        true
                    ),
                    paymentReceived("Segera", false),
                    orderPickedUp("Ambil di toko", false),
                    orderDelivered("Saat barang diambil", false),
                ];
            }
            if (status === "paid") {
                return [
                    orderCreated(
                        formatDate(props.transaction?.created_at),
                        true
                    ),
                    paymentReceived(
                        getDateOrFallback(paid_at, updated_at),
                        true
                    ),
                    orderPickedUp("Ambil di toko", false),
                    orderDelivered("Saat barang diambil", false),
                ];
            }
            if (status === "processing") {
                return [
                    orderCreated(
                        formatDate(props.transaction?.created_at),
                        true
                    ),
                    paymentReceived(
                        getDateOrFallback(paid_at, updated_at),
                        true
                    ),
                    orderPickedUp(
                        getDateOrFallback(picked_up_at, updated_at),
                        true
                    ),
                    orderDelivered("Saat barang diambil", false),
                ];
            }
            if (status === "completed") {
                return [
                    orderCreated(
                        formatDate(props.transaction?.created_at),
                        true
                    ),
                    paymentReceived(
                        getDateOrFallback(paid_at, updated_at),
                        true
                    ),
                    orderPickedUp(
                        getDateOrFallback(picked_up_at, updated_at),
                        true
                    ),
                    orderDelivered(
                        getDateOrFallback(delivered_at, updated_at),
                        true
                    ),
                ];
            }
        }
    }

    // Payment COD
    if (paymentSlug === "cod") {
        if (shippingSlug === "courier") {
            if (status === "pending") {
                return [
                    orderCreated(
                        formatDate(props.transaction?.created_at),
                        true
                    ),
                    orderShipped("Segera", false),
                    orderDelivered(estimateText, false),
                ];
            }
            if (status === "processing") {
                return [
                    orderCreated(
                        formatDate(props.transaction?.created_at),
                        true
                    ),
                    orderShipped(
                        getDateOrFallback(shipped_at, updated_at),
                        true
                    ),
                    orderDelivered(estimateText, false),
                ];
            }
            if (status === "paid") {
                return [
                    orderCreated(
                        formatDate(props.transaction?.created_at),
                        true
                    ),
                    orderShipped(
                        getDateOrFallback(shipped_at, updated_at),
                        true
                    ),
                    orderDelivered(estimateText, false),
                ];
            }
            if (status === "completed") {
                return [
                    orderCreated(
                        formatDate(props.transaction?.created_at),
                        true
                    ),
                    orderShipped(
                        getDateOrFallback(shipped_at, updated_at),
                        true
                    ),
                    orderDelivered(
                        getDateOrFallback(delivered_at, updated_at),
                        true
                    ),
                ];
            }
        } else if (shippingSlug === "pickup") {
            if (status === "pending") {
                return [
                    orderCreated(
                        formatDate(props.transaction?.created_at),
                        true
                    ),
                    orderPickedUp("Ambil di toko", false),
                    paymentReceived("Saat barang diambil", false),
                    orderDelivered("Saat barang diambil", false),
                ];
            }
            if (status === "paid") {
                return [
                    orderCreated(
                        formatDate(props.transaction?.created_at),
                        true
                    ),
                    orderPickedUp(
                        getDateOrFallback(picked_up_at, updated_at),
                        true
                    ),
                    paymentReceived(
                        getDateOrFallback(paid_at, updated_at),
                        true
                    ),
                    orderDelivered("Saat barang diambil", false),
                ];
            }
            if (status === "completed") {
                return [
                    orderCreated(
                        formatDate(props.transaction?.created_at),
                        true
                    ),
                    orderPickedUp(
                        getDateOrFallback(picked_up_at, updated_at),
                        true
                    ),
                    paymentReceived(
                        getDateOrFallback(paid_at, updated_at),
                        true
                    ),
                    orderDelivered(
                        getDateOrFallback(delivered_at, updated_at),
                        true
                    ),
                ];
            }
        }
    }

    // Default fallback
    return [orderCreated(formatDate(props.transaction?.created_at), true)];
}

histories.value = buildHistories();

const progress = ref(0);

// Delay 100 ms
setTimeout(() => {
    // Calculate progress based on done histories
    progress.value =
        (histories.value.filter((h) => h.done).length /
            histories.value.length) *
        100;
}, 100);

function confirmWhatsApp() {
    const phone = "6283861999797";
    const message = `Halo, saya ingin mengkonfirmasi pesanan dengan kode transaksi ${props.transaction?.code}.`;
    const url = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
    window.open(url, "_blank");
}

const emit = defineEmits(["continuePayment"]);
</script>

<template>
    <div class="flex flex-col gap-4">
        <!-- Tracking -->
        <div
            v-if="props.showTracking"
            class="flex flex-col items-center gap-4 py-2 mx-auto w-fit sm:gap-6 sm:py-4"
        >
            <div
                class="flex items-start justify-center gap-4 md:gap-8 lg:gap-12"
            >
                <div
                    v-for="(history, index) in histories"
                    :key="history.date"
                    class="flex flex-col items-center gap-2.5 sm:gap-4"
                >
                    <div
                        v-if="history.icon"
                        v-html="history.icon"
                        class="[&>svg]:size-6 sm:[&>svg]:size-8"
                        :class="{
                            'fill-primary': history.done,
                            'fill-gray-400': !history.done,
                        }"
                    ></div>

                    <div class="flex flex-col items-center gap-0 text-center">
                        <p
                            class="text-sm font-semibold text-gray-800 sm:text-base"
                            :class="{ 'text-primary': history.done }"
                        >
                            {{ history.title }}
                        </p>
                        <p
                            class="text-xs text-gray-600 sm:text-sm"
                            :class="{ 'text-primary': history.done }"
                        >
                            {{ history.date }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Line -->
            <div class="relative w-full h-1.5 bg-gray-200 rounded-full">
                <div
                    class="absolute top-0 left-0 h-full transition-all ease-out rounded-full bg-primary duration-[1s]"
                    :style="{
                        width: `${progress > 100 ? 100 : progress}%`,
                    }"
                ></div>
            </div>
        </div>

        <!-- Warning -->
        <InfoHint
            v-if="
                props.transaction.payment_method.slug === 'transfer' &&
                props.transaction.status === 'pending'
            "
            type="warning"
            class="mx-auto mb-2 max-w-7xl"
            :class="{
                'max-w-none': props.isShowingFromMyStore,
            }"
        >
            <template #content>
                <p>
                    Segera
                    <span
                        class="font-semibold cursor-pointer hover:underline"
                        @click="emit('continuePayment')"
                        >lanjutkan pembayaran</span
                    >
                    agar pesanan tidak dibatalkan.
                </p>
            </template>
        </InfoHint>

        <!-- Info -->
        <!-- <InfoHint
            v-if="
                props.transaction.payment_method.slug === 'transfer' &&
                props.transaction.status === 'paid'
            "
            type="success"
        >
            <template #content>
                <div class="flex items-center justify-between w-full gap-2">
                    <p>
                        Pembayaran Anda telah diterima dan sedang diproses.
                        Konfirmasi pesanan melalui WhatsApp untuk informasi
                        lebih lanjut.
                    </p>
                    <PrimaryButton
                        @click="confirmWhatsApp()"
                        class="!bg-green-600 rounded-lg hover:!bg-green-700 py-2 !text-base !font-bold w-full sm:w-auto"
                    >
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
                        <p class="font-semibold text-white">
                            Konfirmasi Pesanan
                        </p>
                    </PrimaryButton>
                </div>
            </template>
        </InfoHint> -->

        <!-- Details -->
        <LandingSection class="!flex-col !justify-start !min-h-[56vh]">
            <div
                class="flex flex-col-reverse items-center justify-center w-full gap-5 mx-auto xl:flex-row xl:items-start max-w-7xl"
                :class="{
                    'max-w-none': props.isShowingFromMyStore,
                }"
            >
                <!-- Items -->
                <div class="flex flex-col w-full gap-4">
                    <OrderGroup
                        v-for="(item, index) in props.groups"
                        :key="index"
                        :orderGroup="item"
                        :showDivider="index !== props.groups.length - 1"
                    />
                </div>
                <div class="flex flex-col w-full gap-5 xl:max-w-sm">
                    <!-- Summary -->
                    <div
                        class="flex flex-col w-full p-4 outline outline-1 -outline-offset-1 outline-gray-300 rounded-2xl gap-y-3"
                    >
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800">
                                Data Pengguna
                            </h3>
                        </div>
                        <DetailRow
                            name="Nama"
                            :value="props.transaction.user.name"
                        />
                        <DetailRow
                            name="Email"
                            :value="props.transaction.user.email"
                        />
                        <DetailRow
                            name="No. HP"
                            :value="props.transaction.user.phone"
                        />
                    </div>
                    <OrderSummaryCard
                        :transaction="props.transaction"
                        :groups="props.groups"
                        :isLoading="props.isLoading"
                    >
                        <template #additionalInfo v-if="$slots.additionalInfo">
                            <slot name="additionalInfo" />
                        </template>
                        <template #actions v-if="$slots.actions">
                            <slot name="actions" />
                        </template>
                    </OrderSummaryCard>
                </div>
            </div>
        </LandingSection>
    </div>
</template>

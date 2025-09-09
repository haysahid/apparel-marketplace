<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import LandingSection from "@/Components/LandingSection.vue";
import formatDate from "@/plugins/date-formatter";
import OrderGroup from "./OrderGroup.vue";
import OrderSummaryCard from "./OrderSummaryCard.vue";
import DetailRow from "@/Components/DetailRow.vue";

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

const emit = defineEmits(["continuePayment"]);
</script>

<template>
    <div class="p-6 !pt-0 sm:p-12 md:p-[100px] flex flex-col gap-6">
        <!-- Tracking -->
        <div
            v-if="props.showTracking"
            class="flex flex-col items-center gap-4 mx-auto w-fit sm:gap-6"
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
        <div
            v-if="
                props.transaction.payment_method.slug === 'transfer' &&
                props.transaction.status === 'pending'
            "
            class="flex items-center w-full gap-2 p-4 mx-auto text-sm text-yellow-800 border border-yellow-300 rounded-xl bg-yellow-50 sm:text-base max-w-7xl"
        >
            <span>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    class="transition-colors duration-200 size-5 fill-yellow-800 sm:size-6"
                >
                    <path
                        d="M12 17C12.2833 17 12.521 16.904 12.713 16.712C12.905 16.52 13.0007 16.2827 13 16V12C13 11.7167 12.904 11.4793 12.712 11.288C12.52 11.0967 12.2827 11.0007 12 11C11.7173 10.9993 11.48 11.0953 11.288 11.288C11.096 11.4807 11 11.718 11 12V16C11 16.2833 11.096 16.521 11.288 16.713C11.48 16.905 11.7173 17.0007 12 17ZM12 9C12.2833 9 12.521 8.904 12.713 8.712C12.905 8.52 13.0007 8.28267 13 8C12.9993 7.71733 12.9033 7.48 12.712 7.288C12.5207 7.096 12.2833 7 12 7C11.7167 7 11.4793 7.096 11.288 7.288C11.0967 7.48 11.0007 7.71733 11 8C10.9993 8.28267 11.0953 8.52033 11.288 8.713C11.4807 8.90567 11.718 9.00133 12 9ZM12 22C10.6167 22 9.31667 21.7373 8.1 21.212C6.88334 20.6867 5.825 19.9743 4.925 19.075C4.025 18.1757 3.31267 17.1173 2.788 15.9C2.26333 14.6827 2.00067 13.3827 2 12C1.99933 10.6173 2.262 9.31733 2.788 8.1C3.314 6.88267 4.02633 5.82433 4.925 4.925C5.82367 4.02567 6.882 3.31333 8.1 2.788C9.318 2.26267 10.618 2 12 2C13.382 2 14.682 2.26267 15.9 2.788C17.118 3.31333 18.1763 4.02567 19.075 4.925C19.9737 5.82433 20.6863 6.88267 21.213 8.1C21.7397 9.31733 22.002 10.6173 22 12C21.998 13.3827 21.7353 14.6827 21.212 15.9C20.6887 17.1173 19.9763 18.1757 19.075 19.075C18.1737 19.9743 17.1153 20.687 15.9 21.213C14.6847 21.739 13.3847 22.0013 12 22ZM12 20C14.2333 20 16.125 19.225 17.675 17.675C19.225 16.125 20 14.2333 20 12C20 9.76667 19.225 7.875 17.675 6.325C16.125 4.775 14.2333 4 12 4C9.76667 4 7.875 4.775 6.325 6.325C4.775 7.875 4 9.76667 4 12C4 14.2333 4.775 16.125 6.325 17.675C7.875 19.225 9.76667 20 12 20Z"
                    />
                </svg>
            </span>
            <p>
                Segera<span
                    class="font-semibold cursor-pointer hover:underline"
                    @click="emit('continuePayment')"
                >
                    lanjutkan pembayaran </span
                >agar pesanan Anda tidak dibatalkan.
            </p>
        </div>

        <!-- Details -->
        <LandingSection class="!flex-col !justify-start !min-h-[56vh]">
            <div
                class="flex flex-col-reverse items-center justify-center w-full gap-5 mx-auto xl:flex-row xl:items-start sm:gap-8 max-w-7xl"
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

                <div class="flex flex-col w-full gap-5 xl:gap-6 xl:max-w-sm">
                    <!-- Summary -->
                    <div
                        class="flex flex-col w-full p-4 outline outline-1 -outline-offset-1 outline-gray-300 rounded-2xl gap-y-3"
                    >
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800">
                                Data Pemesan
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
                    >
                        <template #additionalInfo v-if="$slots.additionalInfo">
                            <slot name="additionalInfo" />
                        </template>
                        <template #actions>
                            <slot name="actions" />
                        </template>
                    </OrderSummaryCard>
                </div>
            </div>
        </LandingSection>
    </div>
</template>

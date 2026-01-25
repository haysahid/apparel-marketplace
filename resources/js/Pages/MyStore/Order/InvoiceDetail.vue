<script setup lang="ts">
import LandingSection from "@/Components/LandingSection.vue";
import OrderGroup from "@/Pages/Order/OrderGroup.vue";
import InvoiceSummaryCard from "./InvoiceSummaryCard.vue";
import DetailRow from "@/Components/DetailRow.vue";
import InfoHint from "@/Components/InfoHint.vue";
import InvoiceTracking from "./InvoiceTracking.vue";
import StatusChip from "@/Components/StatusChip.vue";
import CopyButton from "@/Components/CopyButton.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import WhatsAppButton from "@/Components/WhatsAppButton.vue";
import { getWhatsAppLink, openWhatsAppChat } from "@/plugins/helpers";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    invoice: {
        type: Object as () => InvoiceEntity,
        required: true,
    },
    items: {
        type: Array as () => TransactionItemEntity[],
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
    shipments: {
        type: Array as () => ShipmentEntity[],
        default: () => [],
    },
});

const emit = defineEmits(["continuePayment"]);
</script>

<template>
    <div class="flex flex-col gap-y-3 gap-x-4">
        <!-- Warning -->
        <InfoHint
            v-if="
                props.invoice.transaction.payment_method.slug == 'transfer' &&
                props.invoice.status === 'pending'
            "
            type="warning"
            class="mx-auto mb-2 max-w-7xl"
            :class="{
                'max-w-none': props.isShowingFromMyStore,
            }"
        >
            <template #content>
                <div
                    v-if="
                        props.invoice.transaction.user_id !=
                            $page.props.auth.user.id &&
                        $page.props.selected_store != null
                    "
                    class="flex items-center justify-between w-full gap-4"
                >
                    <p>Pemesan belum melakukan pembayaran.</p>
                    <WhatsAppButton
                        class="whitespace-nowrap"
                        @click="
                            openWhatsAppChat(
                                props.invoice.transaction.user.phone,
                                `Halo, saya ingin mengkonfirmasi pesanan dengan kode transaksi ${props.invoice.transaction.code}.`,
                            )
                        "
                    >
                        Hubungi Pemesan
                    </WhatsAppButton>
                </div>
                <p v-else>
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

        <!-- Tracking -->
        <InvoiceTracking
            v-if="props.showTracking"
            :invoice="props.invoice"
            class="xl:hidden"
        />

        <!-- Details -->
        <LandingSection class="!flex-col !justify-start !min-h-[56vh]">
            <div
                class="flex flex-col items-center justify-center w-full mx-auto gap-y-3 gap-x-4 xl:flex-row xl:items-start max-w-7xl"
                :class="{
                    'max-w-none': props.isShowingFromMyStore,
                }"
            >
                <div class="flex flex-col w-full gap-y-3 gap-x-4 xl:max-w-sm">
                    <!-- Customer -->
                    <DefaultCard isMain class="flex flex-col w-full gap-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800">
                                Data Pemesan
                            </h3>
                        </div>
                        <DetailRow
                            name="Nama"
                            :value="props.invoice.transaction.user.name"
                        />
                        <DetailRow
                            name="Username"
                            :value="props.invoice.transaction.user.username"
                        />
                        <DetailRow
                            name="Email"
                            :value="props.invoice.transaction.user.email"
                        />
                        <DetailRow
                            name="No. HP"
                            :value="props.invoice.transaction.user.phone"
                        />
                        <a
                            v-if="props.isShowingFromMyStore"
                            :href="
                                getWhatsAppLink(
                                    props.invoice.transaction.user.phone,
                                    `Halo, saya ingin mengkonfirmasi pesanan dengan nomor invoice ${props.invoice.code}.`,
                                )
                            "
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-1"
                        >
                            <SecondaryButton
                                class="w-full text-green-600 border-green-500 whitespace-nowrap hover:bg-green-50 focus:ring-green-500"
                            >
                                <template #prefix>
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="25"
                                        viewBox="0 0 24 25"
                                        class="fill-green-600 size-5 shrink-0"
                                    >
                                        <path
                                            d="M19.0498 5.60488C18.1329 4.67898 17.0408 3.94484 15.8373 3.44524C14.6338 2.94564 13.3429 2.69056 12.0398 2.69488C6.5798 2.69488 2.1298 7.14488 2.1298 12.6049C2.1298 14.3549 2.5898 16.0549 3.4498 17.5549L2.0498 22.6949L7.2998 21.3149C8.7498 22.1049 10.3798 22.5249 12.0398 22.5249C17.4998 22.5249 21.9498 18.0749 21.9498 12.6149C21.9498 9.96488 20.9198 7.47488 19.0498 5.60488ZM12.0398 20.8449C10.5598 20.8449 9.1098 20.4449 7.8398 19.6949L7.5398 19.5149L4.4198 20.3349L5.2498 17.2949L5.0498 16.9849C4.22735 15.6719 3.79073 14.1542 3.7898 12.6049C3.7898 8.06488 7.4898 4.36488 12.0298 4.36488C14.2298 4.36488 16.2998 5.22488 17.8498 6.78488C18.6174 7.54874 19.2257 8.45742 19.6394 9.45821C20.0531 10.459 20.264 11.532 20.2598 12.6149C20.2798 17.1549 16.5798 20.8449 12.0398 20.8449ZM16.5598 14.6849C16.3098 14.5649 15.0898 13.9649 14.8698 13.8749C14.6398 13.7949 14.4798 13.7549 14.3098 13.9949C14.1398 14.2449 13.6698 14.8049 13.5298 14.9649C13.3898 15.1349 13.2398 15.1549 12.9898 15.0249C12.7398 14.9049 11.9398 14.6349 10.9998 13.7949C10.2598 13.1349 9.7698 12.3249 9.6198 12.0749C9.4798 11.8249 9.5998 11.6949 9.7298 11.5649C9.8398 11.4549 9.9798 11.2749 10.0998 11.1349C10.2198 10.9949 10.2698 10.8849 10.3498 10.7249C10.4298 10.5549 10.3898 10.4149 10.3298 10.2949C10.2698 10.1749 9.7698 8.95488 9.5698 8.45488C9.3698 7.97488 9.1598 8.03488 9.0098 8.02488H8.5298C8.3598 8.02488 8.0998 8.08488 7.8698 8.33488C7.6498 8.58488 7.0098 9.18488 7.0098 10.4049C7.0098 11.6249 7.89981 12.8049 8.0198 12.9649C8.1398 13.1349 9.7698 15.6349 12.2498 16.7049C12.8398 16.9649 13.2998 17.1149 13.6598 17.2249C14.2498 17.4149 14.7898 17.3849 15.2198 17.3249C15.6998 17.2549 16.6898 16.7249 16.8898 16.1449C17.0998 15.5649 17.0998 15.0749 17.0298 14.9649C16.9598 14.8549 16.8098 14.8049 16.5598 14.6849Z"
                                        />
                                    </svg>
                                </template>
                                Hubungi Pemesan
                            </SecondaryButton>
                        </a>
                    </DefaultCard>

                    <!-- Invoice Summary -->
                    <InvoiceSummaryCard
                        :invoice="props.invoice"
                        :items="props.items"
                    >
                        <template #additionalInfo v-if="$slots.additionalInfo">
                            <slot name="additionalInfo" />
                        </template>
                        <template #actions>
                            <slot name="actions" />
                        </template>
                    </InvoiceSummaryCard>

                    <!-- Shipments -->
                    <DefaultCard
                        v-if="props.shipments.length > 0"
                        isMain
                        class="flex flex-col w-full gap-y-2"
                    >
                        <h3 class="font-semibold text-gray-800">
                            Informasi Pengiriman
                        </h3>

                        <div class="flex flex-col gap-2">
                            <div
                                v-for="(shipment, index) in props.shipments"
                                :key="shipment.id"
                                class="flex flex-col gap-2"
                            >
                                <DetailRow
                                    name="Nomor Resi"
                                    :value="shipment.tracking_number"
                                >
                                    <template #value>
                                        <div class="flex items-center gap-0.5">
                                            <p
                                                class="text-sm font-semibold text-right text-gray-700"
                                            >
                                                {{ shipment.tracking_number }}
                                            </p>
                                            <CopyButton
                                                :id="`copy-code-tooltip-shipment-${shipment.id}`"
                                                :text="shipment.tracking_number"
                                            />
                                        </div>
                                    </template>
                                </DetailRow>
                                <DetailRow
                                    name="Kurir"
                                    :value="shipment.courier_name"
                                />
                                <DetailRow
                                    name="Biaya Pengiriman"
                                    :value="`Rp ${shipment.shipping_cost.toLocaleString(
                                        'id-ID',
                                    )}`"
                                />
                                <DetailRow name="Status">
                                    <template #value>
                                        <StatusChip
                                            :label="
                                                shipment.status.toUpperCase()
                                            "
                                            :class="{
                                                'text-gray-500 bg-gray-100':
                                                    shipment.status ===
                                                    'pending',
                                                'text-orange-500 bg-orange-100':
                                                    shipment.status ===
                                                    'in_transit',
                                                'text-indigo-500 bg-indigo-100':
                                                    shipment.status ===
                                                    'out_of_delivery',
                                                'text-green-500 bg-green-100':
                                                    shipment.status ===
                                                    'delivered',
                                                'text-red-500 bg-red-100':
                                                    shipment.status ===
                                                        'lost' ||
                                                    shipment.status ===
                                                        'returned',
                                            }"
                                        />
                                    </template>
                                </DetailRow>

                                <div
                                    v-if="index !== props.shipments.length - 1"
                                    class="my-2 border-b border-gray-200"
                                ></div>
                            </div>
                        </div>
                    </DefaultCard>
                </div>

                <div class="flex flex-col w-full gap-y-3 gap-x-4 xl:flex-1">
                    <!-- Tracking -->
                    <InvoiceTracking
                        v-if="props.showTracking"
                        :invoice="props.invoice"
                        class="hidden xl:block"
                    />

                    <!-- Items -->
                    <OrderGroup
                        :orderGroup="{
                            store_id: props.invoice.store_id,
                            store: props.invoice.store,
                            invoice: props.invoice,
                            items: props.items,
                        }"
                        :showSummary="false"
                    />
                </div>
            </div>
        </LandingSection>
    </div>
</template>

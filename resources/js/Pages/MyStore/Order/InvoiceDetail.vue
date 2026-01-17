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
import { openWhatsAppChat } from "@/plugins/helpers";

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
                    <p>Pelanggan belum melakukan pembayaran.</p>
                    <WhatsAppButton
                        class="whitespace-nowrap"
                        @click="
                            openWhatsAppChat(
                                props.invoice.transaction.user.phone,
                                `Halo, saya ingin mengkonfirmasi pesanan dengan kode transaksi ${props.invoice.transaction.code}.`
                            )
                        "
                    >
                        Hubungi Pelanggan
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
                                        'id-ID'
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

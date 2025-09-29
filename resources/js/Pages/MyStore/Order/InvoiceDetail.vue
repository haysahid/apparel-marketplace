<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import LandingSection from "@/Components/LandingSection.vue";
import formatDate from "@/plugins/date-formatter";
import OrderGroup from "@/Pages/Order/OrderGroup.vue";
import InvoiceSummaryCard from "./InvoiceSummaryCard.vue";
import DetailRow from "@/Components/DetailRow.vue";
import InfoHint from "@/Components/InfoHint.vue";
import InvoiceTracking from "./InvoiceTracking.vue";

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
});

const emit = defineEmits(["continuePayment"]);
</script>

<template>
    <div class="flex flex-col gap-4">
        <!-- Tracking -->
        <InvoiceTracking v-if="props.showTracking" :invoice="props.invoice" />

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
                <p
                    v-if="
                        props.invoice.transaction.user_id !=
                            $page.props.auth.user.id &&
                        $page.props.selected_store != null
                    "
                >
                    Pelanggan belum melakukan pembayaran.
                </p>
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

        <!-- Details -->
        <LandingSection class="!flex-col !justify-start !min-h-[56vh]">
            <div
                class="flex flex-col-reverse items-center justify-center w-full gap-5 mx-auto xl:flex-row xl:items-start max-w-7xl"
                :class="{
                    'max-w-none': props.isShowingFromMyStore,
                }"
            >
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

                <div class="flex flex-col w-full gap-5 xl:max-w-sm">
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
                            :value="props.invoice.transaction.user.name"
                        />
                        <DetailRow
                            name="Email"
                            :value="props.invoice.transaction.user.email"
                        />
                        <DetailRow
                            name="No. HP"
                            :value="props.invoice.transaction.user.phone"
                        />
                    </div>
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
                </div>
            </div>
        </LandingSection>
    </div>
</template>

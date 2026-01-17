<script setup lang="ts">
import LandingSection from "@/Components/LandingSection.vue";
import OrderGroup from "./OrderGroup.vue";
import OrderSummaryCard from "./OrderSummaryCard.vue";
import DetailRow from "@/Components/DetailRow.vue";
import InfoHint from "@/Components/InfoHint.vue";
import { openWhatsAppChat } from "@/plugins/helpers";
import WhatsAppButton from "@/Components/WhatsAppButton.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

const props = defineProps({
    transaction: {
        type: Object as () => TransactionEntity,
        required: true,
    },
    groups: {
        type: Array as () => OrderGroupModel[],
        required: true,
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

function confirmWhatsApp() {
    const phone = "6283861999797";
    const message = `Halo, saya ingin mengkonfirmasi pesanan dengan kode transaksi ${props.transaction?.code}.`;
    openWhatsAppChat(phone, message);
}

const emit = defineEmits(["continuePayment"]);
</script>

<template>
    <div class="flex flex-col gap-y-3 gap-x-4">
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
                <div
                    v-if="
                        props.transaction.user_id != $page.props.auth.user.id &&
                        $page.props.selected_store != null
                    "
                    class="flex items-center justify-between w-full gap-4"
                >
                    <p>Pelanggan belum melakukan pembayaran.</p>
                    <WhatsAppButton
                        class="whitespace-nowrap"
                        @click="
                            openWhatsAppChat(
                                props.transaction.user.phone,
                                `Halo, saya ingin mengkonfirmasi pesanan dengan kode transaksi ${props.transaction.code}.`
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

        <!-- Info -->
        <InfoHint
            v-if="
                props.transaction.payment_method.slug === 'transfer' &&
                props.transaction.status === 'paid'
            "
            type="success"
            class="mx-auto mb-2 max-w-7xl"
            :class="{
                'max-w-none': props.isShowingFromMyStore,
            }"
        >
            <template #content>
                <div class="flex items-center justify-between w-full gap-2">
                    <p>
                        Pembayaran Anda telah diterima dan sedang diproses.
                        Konfirmasi pesanan melalui WhatsApp untuk informasi
                        lebih lanjut.
                    </p>
                    <WhatsAppButton @click="confirmWhatsApp()">
                        Konfirmasi Pesanan
                    </WhatsAppButton>
                </div>
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
                            :value="props.transaction.user.name"
                        />
                        <DetailRow
                            name="Username"
                            :value="props.transaction.user.username"
                        />
                        <DetailRow
                            name="Email"
                            :value="props.transaction.user.email"
                        />
                        <DetailRow
                            name="No. HP"
                            :value="props.transaction.user.phone"
                        />
                    </DefaultCard>

                    <!-- Order Summary -->
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

                <!-- Items -->
                <div class="flex flex-col w-full gap-y-3 gap-x-4 xl:flex-1">
                    <OrderGroup
                        v-for="(item, index) in props.groups"
                        :key="index"
                        :orderGroup="item"
                        :showDivider="index !== props.groups.length - 1"
                    />
                </div>
            </div>
        </LandingSection>
    </div>
</template>

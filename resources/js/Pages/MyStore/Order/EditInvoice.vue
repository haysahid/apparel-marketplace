<script setup lang="ts">
import { ref } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import OrderDetail from "@/Pages/Order/OrderDetail.vue";
import ChangeTransactionStatusDialog from "./ChangeTransactionStatusDialog.vue";
import axios from "axios";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import InvoiceDetail from "./InvoiceDetail.vue";

const props = defineProps({
    invoice: {
        type: Object as () => InvoiceEntity,
        required: true,
    },
    items: {
        type: Array as () => TransactionItemEntity[],
        required: true,
    },
    payments: {
        type: Array as () => PaymentEntity[],
        required: true,
    },
});

const showChangeStatusDialog = ref(false);

function changeStatus(newStatus: string) {
    const token = `Bearer ${localStorage.getItem("access_token")}`;

    axios
        .put(
            "/api/my-store/change-order-status",
            {
                invoice_id: props.invoice.id,
                status: newStatus,
            },
            {
                headers: {
                    Authorization: token,
                },
            }
        )
        .then(() => {
            window.location.reload();
        })
        .catch((error) => {
            console.error("Error changing transaction status:", error);
        });
}

window.onpopstate = function () {
    location.reload();
};
</script>

<template>
    <MyStoreLayout :title="`${props.invoice.code}`" :showTitle="true">
        <DefaultCard :isMain="true">
            <InvoiceDetail
                :invoice="props.invoice"
                :items="props.items"
                class="!px-0 !pt-8 md:!px-0"
                :showTracking="props.invoice.status !== 'cancelled'"
            >
                <template #actions>
                    <PrimaryButton
                        @click="showChangeStatusDialog = true"
                        class="w-full py-3"
                    >
                        Ubah Status
                    </PrimaryButton>
                </template>
            </InvoiceDetail>
        </DefaultCard>

        <ChangeTransactionStatusDialog
            :show="showChangeStatusDialog"
            :currentStatus="props.invoice.status"
            :options="[
                {
                    value: 'pending',
                    label: 'PENDING',
                    disabled: false,
                },
                {
                    value: 'paid',
                    label: 'PAID',
                    disabled: false,
                },
                {
                    value: 'processing',
                    label: 'PROCESSING',
                    disabled: false,
                },
                {
                    value: 'completed',
                    label: 'COMPLETED',
                    disabled: false,
                },
                {
                    value: 'cancelled',
                    label: 'CANCELLED',
                    disabled: false,
                },
            ]"
            @change="
                showChangeStatusDialog = false;
                if ($event != props.invoice.status) {
                    changeStatus($event);
                }
            "
            @close="showChangeStatusDialog = false"
        />
    </MyStoreLayout>
</template>

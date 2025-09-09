<script setup lang="ts">
import { ref, onMounted } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import OrderDetail from "@/Pages/Order/OrderDetail.vue";
import ChangeTransactionStatusDialog from "./ChangeTransactionStatusDialog.vue";
import axios from "axios";
import OrderContentRow from "@/Components/OrderContentRow.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
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
});

const showChangeStatusDialog = ref(false);

function changeStatus(newStatus: string) {
    const token = `Bearer ${localStorage.getItem("access_token")}`;

    axios
        .put(
            "/api/admin/change-order-status",
            {
                transaction_id: props.transaction.id,
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
    <MyStoreLayout :title="`${props.transaction.code}`" :showTitle="true">
        <DefaultCard :isMain="true">
            <OrderDetail
                :transaction="props.transaction"
                :groups="props.groups"
                class="!px-0 !pt-8 md:!px-11"
            >
                <template #actions>
                    <PrimaryButton
                        @click="showChangeStatusDialog = true"
                        class="w-full"
                    >
                        Ubah Status
                    </PrimaryButton>
                </template>
            </OrderDetail>
        </DefaultCard>

        <ChangeTransactionStatusDialog
            :show="showChangeStatusDialog"
            :currentStatus="props.transaction.status"
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
                if ($event != props.transaction.status) {
                    changeStatus($event);
                }
            "
            @close="showChangeStatusDialog = false"
        />
    </MyStoreLayout>
</template>

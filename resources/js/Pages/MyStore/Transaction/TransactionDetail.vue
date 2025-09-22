<script setup lang="ts">
import { ref, onMounted } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import OrderDetail from "@/Pages/Order/OrderDetail.vue";
import ChangeTransactionStatusDialog from "./ChangeTransactionStatusDialog.vue";
import axios from "axios";
import OrderContentRow from "@/Components/OrderContentRow.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import SuccessView from "@/Components/SuccessView.vue";

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
    <MyStoreLayout
        title="Detail Transaksi"
        :showTitle="true"
        :breadcrumbs="[
            { text: 'Transaksi', url: '/my-store/transaction', active: false },
            { text: props.transaction.code, active: true },
        ]"
    >
        <DefaultCard :isMain="true">
            <SuccessView
                v-if="route().params.success == 'true'"
                title="Transaksi Berhasil!"
            />
            <OrderDetail
                :data-aos="
                    route().params.success == 'true' ? 'fade-up' : 'none'
                "
                data-aos-delay="1600"
                data-aos-duration="600"
                :transaction="props.transaction"
                :groups="props.groups"
                :showTracking="false"
                :isShowingFromMyStore="true"
                class="!px-0"
            >
                <!-- <template #actions>
                    <PrimaryButton
                        @click="showChangeStatusDialog = true"
                        class="w-full"
                    >
                        Ubah Status
                    </PrimaryButton>
                </template> -->
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

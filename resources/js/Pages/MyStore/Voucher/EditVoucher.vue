<script setup>
import DefaultCard from "@/Components/DefaultCard.vue";
import VoucherForm from "./VoucherForm.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";

const props = defineProps({
    voucher: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <MyStoreLayout title="Ubah Voucher" :showTitle="true">
        <div class="max-w-7xl">
            <DefaultCard>
                <VoucherForm :voucher="props.voucher" />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

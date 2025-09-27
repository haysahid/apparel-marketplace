<script setup>
import VoucherForm from "./VoucherForm.vue";
import DefaultCard from "@/Components/DefaultCard.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
</script>

<template>
    <MyStoreLayout title="Tambah Voucher" :showTitle="true">
        <div class="max-w-7xl">
            <VoucherForm />
        </div>
    </MyStoreLayout>
</template>

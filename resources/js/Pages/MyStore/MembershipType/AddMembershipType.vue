<script setup>
import DefaultCard from "@/Components/DefaultCard.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import MembershipTypeForm from "./MembershipTypeForm.vue";
</script>

<template>
    <MyStoreLayout
        title="Tambah Jenis Keanggotaan"
        :showTitle="true"
        :breadcrumbs="[
            {
                text: 'Jenis Keanggotaan',
                url: '/my-store/membership-type',
                active: false,
            },
            { text: 'Tambah Jenis Keanggotaan', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <MembershipTypeForm />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

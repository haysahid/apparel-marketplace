<script setup>
import DefaultCard from "@/Components/DefaultCard.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import MembershipForm from "./MembershipForm.vue";
</script>

<template>
    <MyStoreLayout
        title="Tambah Keanggotaan"
        :showTitle="true"
        :breadcrumbs="[
            {
                text: 'Keanggotaan',
                url: '/my-store/membership',
                active: false,
            },
            { text: 'Tambah Keanggotaan', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <MembershipForm />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

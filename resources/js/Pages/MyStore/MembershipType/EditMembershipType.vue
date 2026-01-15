<script setup>
import MembershipTypeForm from "./MembershipTypeForm.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

const props = defineProps({
    membershipType: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <MyStoreLayout
        title="Ubah Jenis Keanggotaan"
        :showTitle="true"
        :breadcrumbs="[
            {
                text: 'Jenis Keanggotaan',
                url: '/my-store/membership-type',
                active: false,
            },
            { text: 'Ubah Jenis Keanggotaan', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <MembershipTypeForm :membershipType="props.membershipType" />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

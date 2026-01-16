<script setup>
import MembershipForm from "./MembershipForm.vue";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

const props = defineProps({
    membership: {
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
                url: '/my-store/membership',
                active: false,
            },
            { text: 'Ubah Jenis Keanggotaan', active: true },
        ]"
    >
        <div class="max-w-7xl">
            <DefaultCard>
                <MembershipForm :membership="props.membership" />
            </DefaultCard>
        </div>
    </MyStoreLayout>
</template>

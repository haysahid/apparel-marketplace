<script setup lang="ts">
import LandingSection from "@/Components/LandingSection.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LandingLayout from "@/Layouts/LandingLayout.vue";
import { Link } from "@inertiajs/vue3";
import { useCartStore } from "@/stores/cart-store";
import OrderForm from "./Cart/OrderForm.vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import JoinUs from "@/Components/JoinUs.vue";
import { router } from "@inertiajs/vue3";
import CheckoutGroup from "./Cart/CheckoutGroup.vue";
import GuestForm from "./Cart/GuestForm.vue";
import { useOrderStore } from "@/stores/order-store";
import { computed, ref } from "vue";
import DialogModal from "@/Components/DialogModal.vue";
import DetailRow from "@/Components/DetailRow.vue";
import CustomPageProps from "@/types/model/CustomPageProps";

const page = usePage<CustomPageProps>();

if (route().params.order_id) {
    router.visit(route("order.success", { order_id: route().params.order_id }));
}

const cartStore = useCartStore();

function syncCart() {
    if (cartStore.groupHasSelectedItems.length > 0) {
        let cartGroups = cartStore.groups;

        for (const group of cartGroups) {
            delete group.store;

            for (const item of group.items) {
                // Remove the 'store' property from each item
                if (item.store) {
                    delete item.store;
                }

                // Remove the 'variant' property from each item
                if (item.variant) {
                    delete item.variant;
                }
            }
        }

        const syncCart = JSON.parse(JSON.stringify(cartGroups));

        axios
            .post("/api/sync-cart", {
                cart_groups: syncCart,
            })
            .then((response) => {
                cartStore.updateAllGroups(response.data.result);
            });
    }
}
syncCart();

const orderStore = useOrderStore();

const customer = computed(() => {
    return page.props.auth.user
        ? {
              name: page.props.auth.user.name,
              email: page.props.auth.user.email,
              phone: page.props.auth.user.phone,
          }
        : {
              name: orderStore.form.guest_name,
              email: orderStore.form.guest_email,
              phone: orderStore.form.guest_phone,
          };
});

const showGuestForm = ref(false);
</script>

<template>
    <LandingLayout :title="`Checkout`">
        <div
            class="p-6 sm:p-12 md:px-[100px] md:py-[60px] flex flex-col gap-2 sm:gap-3"
            :class="{
                'min-h-[60vh] items-center justify-center gap-4':
                    cartStore.groups.length == 0,
            }"
        >
            <h1 class="text-2xl font-bold text-start sm:text-center">
                Checkout Keranjang
            </h1>
            <div
                v-if="cartStore.groups.length == 0"
                class="flex flex-col items-center gap-y-6"
            >
                <p class="text-sm text-center text-gray-700">
                    Anda belum menambahkan produk ke keranjang.
                </p>
                <Link :href="route('catalog')">
                    <PrimaryButton class="py-2.5 px-5 mx-auto">
                        Mulai Belanja
                    </PrimaryButton>
                </Link>
            </div>
            <p class="text-sm text-gray-700 text-start sm:text-center" v-else>
                Periksa kembali sebelum buat pesanan.
            </p>
        </div>

        <div
            data-aos="fade-up"
            data-aos-duration="600"
            class="p-6 !pt-0 sm:p-12 md:p-[100px] flex flex-col gap-4 sm:gap-12"
        >
            <LandingSection
                v-if="cartStore.groupHasSelectedItems.length > 0"
                class="!items-start !justify-start"
            >
                <div
                    class="flex flex-col items-center justify-center w-full gap-5 mx-auto lg:flex-row lg:items-start sm:gap-8 max-w-7xl"
                >
                    <!-- Cart Items -->
                    <div class="flex flex-col w-full gap-4">
                        <CheckoutGroup
                            v-for="(
                                cartGroup, index
                            ) in cartStore.groupHasSelectedItems"
                            :key="index"
                            :cartGroup="cartGroup"
                            @selectVoucher="
                                (voucher) => {
                                    cartStore.updateGroup({
                                        ...cartGroup,
                                        voucher: voucher,
                                    });
                                }
                            "
                            @removeVoucher="
                                cartStore.updateGroup({
                                    ...cartGroup,
                                    voucher: null,
                                })
                            "
                        />
                    </div>

                    <!-- Detail Order -->
                    <div class="flex flex-col w-full gap-6 lg:max-w-sm">
                        <div
                            class="flex flex-col w-full p-4 outline outline-1 -outline-offset-1 outline-gray-300 rounded-2xl gap-y-3"
                        >
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-800">
                                    Data Pemesan
                                </h3>
                                <button
                                    v-if="!page.props.auth.user"
                                    class="text-sm text-blue-500 hover:underline"
                                    @click="showGuestForm = true"
                                >
                                    Ubah
                                </button>
                            </div>
                            <DetailRow name="Nama" :value="customer.name" />
                            <DetailRow name="Email" :value="customer.email" />
                            <DetailRow name="No. HP" :value="customer.phone" />
                        </div>

                        <OrderForm />
                    </div>
                </div>
            </LandingSection>

            <!-- Join Us -->
            <LandingSection id="join">
                <JoinUs />
            </LandingSection>
        </div>

        <DialogModal
            :show="showGuestForm"
            maxWidth="sm"
            @close="showGuestForm = false"
        >
            <template #content>
                <GuestForm :isEdit="true" @submit="showGuestForm = false" />
            </template>
        </DialogModal>
    </LandingLayout>
</template>

<script setup lang="ts">
import LandingSection from "@/Components/LandingSection.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LandingLayout from "@/Layouts/LandingLayout.vue";
import CartItem from "./Cart/CartItem.vue";
import { Link } from "@inertiajs/vue3";
import { useCartStore } from "@/stores/cart-store";
import OrderForm from "./Cart/OrderForm.vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import QuantityInput from "@/Components/QuantityInput.vue";
import DeleteConfirmationDialog from "@/Components/DeleteConfirmationDialog.vue";
import JoinUs from "@/Components/JoinUs.vue";
import { router } from "@inertiajs/vue3";
import CartGroup from "./Cart/CartGroup.vue";
import CartForm from "./Cart/CartForm.vue";

const page = usePage();
if (page.props.flash.access_token) {
    localStorage.setItem("access_token", page.props.flash.access_token);
}

if (route().params.order_id) {
    router.visit(route("order.success", { order_id: route().params.order_id }));
}

const cartStore = useCartStore();

function syncCart() {
    if (cartStore.groups.length > 0) {
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
            .post(`${page.props.ziggy.url}/api/sync-cart`, {
                cart_groups: syncCart,
            })
            .then((response) => {
                cartStore.updateAllGroups(response.data.result);
            });
    }
}
syncCart();

function formatPrice(price = 0) {
    return price.toLocaleString("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    });
}
</script>

<template>
    <LandingLayout :title="`Keranjang Saya (${cartStore.groups.length})`">
        <div
            class="p-6 sm:p-12 md:px-[100px] md:py-[60px] flex flex-col gap-2 sm:gap-3 bg-secondary-box"
            :class="{
                'min-h-[60vh] items-center justify-center gap-4':
                    cartStore.groups.length == 0,
            }"
        >
            <h1
                class="text-2xl font-bold text-start sm:text-center sm:text-3xl"
            >
                {{
                    cartStore.groups.length > 0
                        ? `Keranjang Saya (${cartStore.groups.length} item)`
                        : "Keranjang Kosong"
                }}
            </h1>
            <div
                v-if="cartStore.groups.length == 0"
                class="flex flex-col items-center gap-y-6"
            >
                <p class="text-sm text-center text-gray-700 sm:text-base">
                    Anda belum menambahkan produk ke keranjang.
                </p>
                <Link :href="route('catalog')">
                    <PrimaryButton class="py-2.5 px-5 mx-auto">
                        Mulai Belanja
                    </PrimaryButton>
                </Link>
            </div>
            <p
                class="text-sm text-gray-700 text-start sm:text-center sm:text-base"
                v-else
            >
                Periksa kembali sebelum buat pesanan.
            </p>
        </div>

        <div
            data-aos="fade-up"
            data-aos-duration="600"
            class="p-6 sm:p-12 md:p-[100px] md:!pt-[80px] flex flex-col gap-12 lg:gap-20"
        >
            <LandingSection
                v-if="cartStore.groups.length > 0"
                class="!items-start !justify-start"
            >
                <div
                    class="flex flex-col items-center justify-center w-full gap-5 mx-auto lg:flex-row lg:items-start sm:gap-8 max-w-7xl"
                >
                    <!-- Cart Items -->
                    <div class="flex flex-col w-full gap-4">
                        <CartGroup
                            v-for="(cartGroup, index) in cartStore.groups"
                            :key="index"
                            :cartGroup="cartGroup"
                        />
                    </div>

                    <!-- Detail Order -->
                    <CartForm />
                </div>
            </LandingSection>

            <!-- Join Us -->
            <LandingSection id="join">
                <JoinUs />
            </LandingSection>
        </div>
    </LandingLayout>
</template>

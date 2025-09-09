<script setup lang="ts">
import { ref } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useCartStore } from "@/stores/cart-store";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import BaseDialog from "@/Components/BaseDialog.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import DialogModal from "@/Components/DialogModal.vue";
import GuestForm from "./GuestForm.vue";

const page = usePage();
const cartStore = useCartStore();

const showErrorDialog = ref(false);
const errorMessage = ref(null);

function openErrorDialog(message: string) {
    errorMessage.value = message;
    showErrorDialog.value = true;
}

function closeErrorDialog() {
    showErrorDialog.value = false;
    errorMessage.value = null;
}

const showAuthWarning = ref(false);
const showGuestForm = ref(false);

const submit = () => {
    if (!page.props.auth.user) {
        showAuthWarning.value = true;
        return;
    }

    if (cartStore.selectedItems.length === 0) {
        openErrorDialog("Tidak ada item yang dipilih untuk pemesanan.");
        return;
    }

    router.visit(route("checkout"));
};
</script>

<template>
    <div
        class="w-full lg:w-[480px] outline outline-1 -outline-offset-1 outline-gray-300 rounded-2xl p-4 gap-y-4 flex flex-col gap-4"
    >
        <h3 class="font-semibold text-gray-800">Ringkasan Pemesanan</h3>

        <!-- Summary -->
        <div class="flex flex-col gap-y-2">
            <!-- Sub Total -->
            <div class="flex items-center justify-between">
                <p class="text-gray-700">Sub Total</p>
                <p class="font-semibold text-primary">
                    {{
                        cartStore.subTotal.toLocaleString("id-ID", {
                            style: "currency",
                            currency: "IDR",
                            minimumFractionDigits: 0,
                        })
                    }}
                </p>
            </div>

            <PrimaryButton
                class="w-full py-3 mt-2"
                :disabled="cartStore.selectedItems.length == 0"
                @click="submit"
            >
                Checkout
            </PrimaryButton>
        </div>

        <BaseDialog
            :show="showAuthWarning"
            title="Masuk atau Lanjutkan Sebagai Tamu?"
            description="Silakan pilih untuk masuk atau melanjutkan sebagai tamu."
            positiveButtonText="Masuk"
            negativeButtonText="Sebagai Tamu"
            :reverseButton="true"
            @close="showAuthWarning = false"
            @positiveClicked="
                showAuthWarning = false;
                $inertia.visit(
                    route('login', {
                        redirect: route('my-cart'),
                    })
                );
            "
            @negativeClicked="showGuestForm = true"
        />

        <DialogModal
            :show="showGuestForm"
            maxWidth="sm"
            @close="showGuestForm = false"
        >
            <template #content>
                <GuestForm
                    @submit="
                        showAuthWarning = false;
                        showGuestForm = false;
                        $inertia.visit(route('checkout.guest'));
                    "
                />
            </template>
        </DialogModal>

        <ErrorDialog
            v-if="showErrorDialog"
            :title="errorMessage"
            :show="showErrorDialog"
            @close="closeErrorDialog"
        />
    </div>
</template>

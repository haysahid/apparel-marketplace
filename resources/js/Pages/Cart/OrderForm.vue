<script setup lang="ts">
import { ref, computed, watch } from "vue";
import Chip from "@/Components/Chip.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";
import { usePage, router } from "@inertiajs/vue3";
import { useCartStore } from "@/stores/cart-store";
import { useOrderStore } from "@/stores/order-store";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import BaseDialog from "@/Components/BaseDialog.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import useDebounce from "@/plugins/debounce";
import InputGroup from "@/Components/InputGroup.vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import DetailRow from "@/Components/DetailRow.vue";
import cookieManager from "@/plugins/cookie-manager";
import CustomPageProps from "@/types/model/CustomPageProps";

const page = usePage<CustomPageProps>();
const cartStore = useCartStore();
const orderStore = useOrderStore();

const isGuest = page.props.isGuest == true;

const checkoutStatus = ref(null);

const showErrorDialog = ref(false);
const errorMessage = ref(null);

function openErrorDialog(message: string) {
    errorMessage.value = message;
    showErrorDialog.value = true;
}

function closeErrorDialog() {
    showErrorDialog.value = false;
}

const paymentMethods = page.props.paymentMethods as PaymentMethodEntity[];
const shippingMethods = page.props.shippingMethods as ShippingMethodEntity[];

const destinationSearch = ref("");
const destinations = ref([]);

function getDestinations(search) {
    axios
        .get("/api/destinations", {
            params: {
                search: search,
            },
        })
        .then((response) => {
            destinations.value = response.data.result;
        })
        .catch((error) => {});
}

const debouncedGetDestinations = useDebounce();

watch(
    () => destinationSearch.value,
    (newValue) => {
        if (newValue) {
            debouncedGetDestinations(() => {
                getDestinations(newValue);
            }, 600);
        } else {
            destinations.value = [];
        }
    }
);

const form = useForm({
    payment_method: orderStore.form.payment_method,
    shipping_method: orderStore.form.shipping_method,
    destination_id: orderStore.form.destination_id || null,
    destination: orderStore.form.destination,
    address: orderStore.form.address,
    estimated_delivery: null,
});

function getShippingCost() {
    if (form.shipping_method?.slug !== "courier") {
        cartStore.updateGroups(
            cartStore.groups.map((group) => {
                group.shipping = null;
                return group;
            })
        );
        return;
    }

    if (!cartStore.selectedItems.length) {
        return;
    }

    axios
        .get("/api/shipping-cost", {
            params: {
                destination: form.destination_id,
                store_ids: cartStore.groupHasSelectedItems
                    .map((group) => group.store_id)
                    .join(","),
            },
        })
        .then((response) => {
            const shippings = response.data
                .result as ShippingResponseItemModel[];

            let groupHasSelectedItems = cartStore.groupHasSelectedItems;

            groupHasSelectedItems.forEach((group) => {
                const foundShipping = shippings.find(
                    (item) => item.store_id === group.store_id
                );
                if (foundShipping) {
                    group.shipping = foundShipping.shipping;
                }
            });

            cartStore.updateGroups(groupHasSelectedItems);
        })
        .catch((error) => {
            openErrorDialog("Gagal mendapatkan biaya pengiriman");
        });
}

const updateLocalForm = () => {
    orderStore.updateForm({
        ...orderStore.form,
        payment_method: form.payment_method,
        shipping_method: form.shipping_method,
        destination_id: form.destination_id,
        destination: form.destination,
        address: form.address,
    } as OrderDetailFormModel);
};

// Initialize form with existing order data if available
if (orderStore.form.destination_id) {
    getShippingCost();
}

watch(
    () => form.data(),
    (newForm) => {
        updateLocalForm();
    }
);

const total = computed(() => {
    if (form.shipping_method?.slug == "courier") {
        return (
            cartStore.subTotal -
            cartStore.totalGroupDiscount +
            cartStore.totalShippingCost
        );
    }
    return cartStore.subTotal - cartStore.totalGroupDiscount;
});

const showAuthWarning = ref(false);

function validateForm() {
    let valid = true;
    if (!form.payment_method) {
        form.errors.payment_method = "Metode pembayaran harus dipilih";
        valid = false;
    }
    if (!form.shipping_method) {
        form.errors.shipping_method = "Metode pengiriman harus dipilih";
        valid = false;
    }
    if (form.shipping_method?.slug == "courier") {
        if (!form.destination_id) {
            form.errors.destination_id = "Alamat pengiriman harus dipilih";
            valid = false;
        }
        if (!form.address) {
            form.errors.address = "Alamat lengkap harus diisi";
            valid = false;
        }
    }
    return valid;
}

const submit = () => {
    if (route().current("checkout") && !page.props.auth.user) {
        showAuthWarning.value = true;
        return;
    }

    if (!validateForm()) {
        return;
    }

    checkoutStatus.value = "loading";

    const data = {
        cart_groups: cartStore.groupHasSelectedItems.map((group) => ({
            store_id: group.store_id,
            voucher_code: group.voucher?.code || null,
            items: group.items
                .filter((item) => item.selected)
                .map((item) => ({
                    product_id: item.product_id,
                    variant_id: item.variant_id,
                    quantity: item.quantity,
                })),
        })),
        payment_method_id: form.payment_method?.id,
        shipping_method_id: form.shipping_method?.id,
        destination_id: form.destination_id,
        destination_label: form.destination?.label,
        province_name: form.destination?.province_name,
        city_name: form.destination?.city_name,
        district_name: form.destination?.district_name,
        subdistrict_name: form.destination?.subdistrict_name,
        zip_code: form.destination?.zip_code,
        address: form.address,
    };

    if (isGuest) {
        data["guest_name"] = orderStore.form.guest_name;
        data["guest_email"] = orderStore.form.guest_email;
        data["guest_phone"] = orderStore.form.guest_phone;
    }

    axios
        .post(isGuest ? "/api/checkout-guest" : "/api/checkout", data, {
            headers: {
                authorization: `Bearer ${cookieManager.getItem(
                    "access_token"
                )}`,
            },
        })
        .then((response) => {
            const result = response.data.result;
            cartStore.removeSelectedItems();

            checkoutStatus.value = "success";

            router.visit(
                route(isGuest ? "order.success.guest" : "order.success", {
                    transaction_code: result.transaction.code,
                    show_snap: result.payment.midtrans_snap_token
                        ? true
                        : false,
                })
            );
        })
        .catch((error) => {
            checkoutStatus.value = "error";
            if (error.response.status == 422) {
                form.errors = error.response.data.errors || {};
            } else {
                openErrorDialog(
                    error.response.data.meta.message || "Terjadi kesalahan"
                );
            }
        });
};
</script>

<template>
    <div
        class="flex flex-col w-full gap-4 p-4 outline outline-1 -outline-offset-1 outline-gray-300 rounded-2xl gap-y-4"
    >
        <h3 class="font-semibold text-gray-800">Detail Pemesanan</h3>

        <div class="flex flex-col gap-y-4">
            <!-- Payment Method -->
            <InputGroup for="payment-method" label="Metode Pembayaran">
                <div class="flex flex-wrap gap-2">
                    <Chip
                        v-for="payment in paymentMethods"
                        :key="payment.id"
                        :label="payment.name"
                        :selected="form.payment_method?.id == payment.id"
                        @click="form.payment_method = payment"
                    />
                </div>
            </InputGroup>

            <!-- Shipping Method -->
            <InputGroup for="shipping-method" label="Metode Pengiriman">
                <div class="flex flex-wrap gap-2">
                    <Chip
                        v-for="shipping in shippingMethods"
                        :key="shipping.id"
                        :label="shipping.name"
                        :selected="form.shipping_method?.id == shipping.id"
                        @click="
                            form.shipping_method = shipping;
                            if (form.destination_id) {
                                getShippingCost();
                            }
                        "
                    />
                </div>
            </InputGroup>

            <template v-if="form.shipping_method?.slug == 'courier'">
                <!-- Destination -->
                <InputGroup for="destination" label="Alamat Pengiriman">
                    <DropdownSearchInput
                        id="destination"
                        :modelValue="
                            form.destination_id
                                ? {
                                      label: form.destination?.label,
                                      value: form.destination_id,
                                  }
                                : null
                        "
                        :options="
                            destinations.map((destination) => ({
                                label: destination.label,
                                value: destination.id,
                            }))
                        "
                        placeholder="Cari Alamat Pengiriman"
                        required
                        type="textarea"
                        :error="form.errors.destination_id"
                        @update:modelValue="
                            (option) => {
                                form.destination_id = option.value;
                                form.destination = destinations.find(
                                    (d) => d.id === option.value
                                );
                                form.errors.destination_id = null;

                                getShippingCost();
                            }
                        "
                        @search="destinationSearch = $event"
                        @clear="
                            form.destination_id = null;
                            form.destination = null;
                            form.errors.destination_id = null;
                        "
                    />
                </InputGroup>

                <!-- Address -->
                <InputGroup for="address" label="Alamat Lengkap">
                    <TextAreaInput
                        id="address"
                        v-model="form.address"
                        class="w-full"
                        placeholder="Masukkan alamat lengkap"
                        @update:modelValue="form.errors.address = null"
                        :error="form.errors?.address"
                    />
                    <p
                        v-if="form.estimated_delivery"
                        class="text-sm text-gray-500"
                    >
                        *Estimasi {{ form.estimated_delivery }} hari kerja
                    </p>
                </InputGroup>
            </template>

            <!-- Divider -->
            <div class="my-2 border-b border-gray-300"></div>

            <!-- Summary -->
            <div class="flex flex-col gap-y-2">
                <!-- Sub Total -->
                <DetailRow
                    name="Sub Total"
                    :value="
                        cartStore.subTotal.toLocaleString('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0,
                        })
                    "
                />

                <!-- Discount -->
                <DetailRow
                    name="Diskon"
                    :value="`- ${cartStore.totalGroupDiscount.toLocaleString(
                        'id-ID',
                        {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0,
                        }
                    )}`"
                />

                <!-- Shipping Cost -->
                <DetailRow
                    name="Biaya Pengiriman"
                    :value="
                        (cartStore.selectedItems.length > 0 &&
                        form.shipping_method?.slug == 'courier'
                            ? cartStore.totalShippingCost
                            : 0
                        ).toLocaleString('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0,
                        })
                    "
                />

                <!-- Total -->
                <div class="flex items-center justify-between">
                    <p class="font-bold text-gray-700">Total</p>
                    <p class="font-bold text-primary">
                        {{
                            (cartStore.selectedItems.length > 0
                                ? total
                                : 0
                            ).toLocaleString("id-ID", {
                                style: "currency",
                                currency: "IDR",
                                minimumFractionDigits: 0,
                            })
                        }}
                    </p>
                </div>

                <PrimaryButton
                    class="w-full py-3 mt-2"
                    :disabled="
                        !form.payment_method ||
                        !form.shipping_method ||
                        cartStore.selectedItems.length == 0 ||
                        checkoutStatus === 'loading'
                    "
                    @click="submit"
                >
                    Pesan Sekarang
                </PrimaryButton>
            </div>

            <!-- Divider -->
            <!-- <div class="my-2 border-b border-gray-300"></div> -->

            <!-- Note -->
            <!-- <p class="text-sm text-gray-500">Catatan:</p>
            <ul
                class="flex flex-col pl-4 text-sm text-gray-500 list-disc list-outside gap-y-2"
            >
                <li>
                    Pastikan alamat dan informasi pemesanan sudah benar sebelum
                    melanjutkan ke pembayaran.
                </li>
                <li>Total harga belum termasuk biaya layanan transfer.</li>
            </ul> -->
        </div>

        <BaseDialog
            :show="showAuthWarning"
            title="Masuk untuk Melanjutkan"
            description="Anda harus masuk untuk melanjutkan pemesanan. Silakan masuk atau daftar akun terlebih dahulu."
            positiveButtonText="Masuk"
            negativeButtonText="Daftar"
            @close="showAuthWarning = false"
            @positiveClicked="
                showAuthWarning = false;
                $inertia.visit(
                    route('login', {
                        redirect: route('my-cart'),
                    })
                );
            "
            @negativeClicked="
                showAuthWarning = false;
                $inertia.visit(
                    route('register', {
                        redirect: route('my-cart'),
                    })
                );
            "
        />

        <ErrorDialog
            :title="errorMessage"
            :show="showErrorDialog"
            @close="closeErrorDialog"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import Chip from "@/Components/Chip.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import Dropdown from "@/Components/Dropdown.vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";
import { usePage, router } from "@inertiajs/vue3";
import { useCartStore } from "@/stores/cart-store";
import { useOrderStore } from "@/stores/order-store";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import BaseDialog from "@/Components/BaseDialog.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import useDebounce from "@/plugins/debounce";

const page = usePage();
const cartStore = useCartStore();
const orderStore = useOrderStore();

const checkoutStatus = ref(null);
const checkoutResponse = ref({
    transaction: null,
    invoices: [],
    payment: null,
});

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

const paymentMethods = page.props.paymentMethods as PaymentMethodEntity[];
const shippingMethods = page.props.shippingMethods as ShippingMethodEntity[];

const destinationSearch = ref("");
const isDestinationDropdownOpen = ref(false);
const destinations = ref([]);

function getDestinations(search) {
    axios
        .get(`${page.props.ziggy.url}/api/destinations`, {
            params: {
                search: search,
            },
        })
        .then((response) => {
            destinations.value = response.data.result;
        })
        .catch((error) => {});
}

const debouncedGetDestinations = useDebounce(getDestinations, 400);

watch(
    () => destinationSearch.value,
    (newValue) => {
        if (newValue) {
            debouncedGetDestinations(newValue);
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
        .get(`${page.props.ziggy.url}/api/shipping-cost`, {
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

const submit = () => {
    if (!page.props.auth.user) {
        showAuthWarning.value = true;
        return;
    }

    checkoutStatus.value = "loading";

    axios
        .post(
            `${page.props.ziggy.url}/api/checkout`,
            {
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
            },
            {
                headers: {
                    authorization: `Bearer ${localStorage.getItem(
                        "access_token"
                    )}`,
                },
            }
        )
        .then((response) => {
            checkoutResponse.value = response.data.result;
            cartStore.removeSelectedItems();

            checkoutStatus.value = "success";

            router.visit(
                route("order.success", {
                    transaction_code: checkoutResponse.value.transaction.code,
                    show_snap: checkoutResponse.value.payment
                        .midtrans_snap_token
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
                openErrorDialog("Terjadi kesalahan");
            }
        });
};
</script>

<template>
    <div
        class="w-full lg:w-[480px] outline outline-1 -outline-offset-1 outline-gray-300 rounded-2xl p-4 gap-y-4 flex flex-col gap-4"
    >
        <h3 class="text-lg font-semibold text-gray-700">Detail Pemesanan</h3>

        <div class="flex flex-col gap-y-3">
            <!-- Payment Method -->
            <div class="flex flex-col gap-y-2">
                <InputLabel
                    for="payment-method"
                    value="Metode Pembayaran"
                    class="!text-gray-500"
                />
                <div class="flex flex-wrap gap-2">
                    <Chip
                        v-for="payment in paymentMethods"
                        :key="payment.id"
                        :label="payment.name"
                        :selected="form.payment_method?.id == payment.id"
                        @click="form.payment_method = payment"
                    />
                </div>
            </div>

            <!-- Shipping Method -->
            <div class="flex flex-col gap-y-2">
                <InputLabel
                    for="shipping-method"
                    value="Metode Pengiriman"
                    class="!text-gray-500"
                />
                <div class="flex flex-wrap gap-2">
                    <Chip
                        v-for="shipping in shippingMethods"
                        :key="shipping.id"
                        :label="shipping.name"
                        :selected="form.shipping_method?.id == shipping.id"
                        @click="
                            form.shipping_method = shipping;
                            getShippingCost();
                        "
                    />
                </div>
            </div>

            <template v-if="form.shipping_method?.slug == 'courier'">
                <!-- Destination -->
                <div class="flex flex-col gap-y-2">
                    <InputLabel
                        for="destination"
                        value="Alamat Pengiriman"
                        class="!text-gray-500"
                    />
                    <Dropdown
                        v-if="destinations"
                        id="destination"
                        v-model="form.destination_id"
                        :options="destinations"
                        option-label="name"
                        option-value="id"
                        placeholder="Pilih Alamat Pengiriman"
                        align="left"
                        required
                        :error="form.errors.destination_id"
                        @update:modelValue="form.errors.destination_id = null"
                        @onOpen="isDestinationDropdownOpen = true"
                        @onClose="isDestinationDropdownOpen = false"
                    >
                        <template #trigger>
                            <TextAreaInput
                                :modelValue="
                                    form.destination_id &&
                                    !isDestinationDropdownOpen
                                        ? form.destination?.label
                                        : destinationSearch
                                "
                                @update:modelValue="
                                    form.destination_id &&
                                    !isDestinationDropdownOpen
                                        ? null
                                        : (destinationSearch = $event)
                                "
                                class="w-full"
                                placeholder="Cari Alamat Pengiriman"
                                :rows="1"
                                :preventNewLine="true"
                                :error="
                                    form.errors?.destination_id
                                        ? form.errors?.destination_id[0] || null
                                        : null
                                "
                            >
                                <template #suffix>
                                    <button
                                        v-if="
                                            form.destination_id &&
                                            !isDestinationDropdownOpen
                                        "
                                        type="button"
                                        class="absolute p-[7px] text-gray-400 bg-white rounded-full top-1 right-1 hover:bg-gray-100 transition-all duration-300 ease-in-out"
                                        @click="
                                            form.destination_id = null;
                                            destinationSearch = '';
                                            cartStore.updateGroups(
                                                cartStore.groups.map(
                                                    (group) => {
                                                        group.shipping = null;
                                                        return group;
                                                    }
                                                )
                                            );
                                        "
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            class="size-5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        v-else
                                        type="button"
                                        class="absolute p-2 top-1.5 right-1"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            class="size-4 fill-gray-400"
                                        >
                                            <path
                                                d="M18.6054 7.3997C18.4811 7.273 18.3335 7.17248 18.1709 7.10389C18.0084 7.0353 17.8342 7 17.6583 7C17.4823 7 17.3081 7.0353 17.1456 7.10389C16.9831 7.17248 16.8355 7.273 16.7112 7.3997L11.4988 12.7028L6.28648 7.3997C6.03529 7.14415 5.69462 7.00058 5.33939 7.00058C4.98416 7.00058 4.64348 7.14415 4.3923 7.3997C4.14111 7.65526 4 8.00186 4 8.36327C4 8.72468 4.14111 9.07129 4.3923 9.32684L10.5585 15.6003C10.6827 15.727 10.8304 15.8275 10.9929 15.8961C11.1554 15.9647 11.3296 16 11.5055 16C11.6815 16 11.8557 15.9647 12.0182 15.8961C12.1807 15.8275 12.3284 15.727 12.4526 15.6003L18.6188 9.32684C19.1293 8.80747 19.1293 7.93274 18.6054 7.3997Z"
                                            />
                                        </svg>
                                    </button>
                                </template>
                            </TextAreaInput>
                        </template>
                        <template #content>
                            <ul class="overflow-y-auto max-h-60">
                                <li
                                    v-for="destination in destinations"
                                    :key="destination.id"
                                    @click="
                                        isDestinationDropdownOpen = false;

                                        form.destination_id = destination.id;
                                        form.destination = destination;
                                        destinationSearch = '';

                                        getShippingCost();
                                    "
                                    class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                                >
                                    {{ destination.label }}
                                </li>
                            </ul>
                        </template>
                    </Dropdown>
                </div>

                <!-- Address -->
                <div class="flex flex-col gap-y-2">
                    <InputLabel
                        for="address"
                        value="Alamat Lengkap"
                        class="!text-gray-500"
                    />
                    <TextAreaInput
                        id="address"
                        v-model="form.address"
                        class="w-full"
                        placeholder="Masukkan alamat lengkap"
                        @update:modelValue="form.errors.address = null"
                        :error="
                            form.errors?.address
                                ? form.errors?.address[0] || null
                                : null
                        "
                    />
                    <p
                        v-if="form.estimated_delivery"
                        class="text-sm text-gray-500"
                    >
                        *Estimasi {{ form.estimated_delivery }} hari kerja
                    </p>
                </div>
            </template>

            <!-- Divider -->
            <div class="my-2 border-b border-gray-300"></div>

            <!-- Summary -->
            <div class="flex flex-col gap-y-2">
                <!-- Sub Total -->
                <div class="flex items-center justify-between">
                    <p class="text-gray-700">Sub Total</p>
                    <p class="font-semibold text-gray-700">
                        {{
                            cartStore.subTotal.toLocaleString("id-ID", {
                                style: "currency",
                                currency: "IDR",
                                minimumFractionDigits: 0,
                            })
                        }}
                    </p>
                </div>

                <!-- Discount -->
                <div class="flex items-center justify-between">
                    <p class="text-gray-700">Diskon</p>
                    <p class="text-gray-700">
                        -
                        {{
                            cartStore.totalGroupDiscount.toLocaleString(
                                "id-ID",
                                {
                                    style: "currency",
                                    currency: "IDR",
                                    minimumFractionDigits: 0,
                                }
                            )
                        }}
                    </p>
                </div>

                <!-- Shipping Cost -->
                <div class="flex items-center justify-between">
                    <p class="text-gray-700">Biaya Pengiriman</p>
                    <p class="text-gray-700">
                        {{
                            (cartStore.selectedItems.length > 0 &&
                            form.shipping_method?.slug == "courier"
                                ? cartStore.totalShippingCost
                                : 0
                            ).toLocaleString("id-ID", {
                                style: "currency",
                                currency: "IDR",
                                minimumFractionDigits: 0,
                            })
                        }}
                    </p>
                </div>

                <!-- Total -->
                <div class="flex items-center justify-between">
                    <p class="text-lg font-bold text-gray-700">Total</p>
                    <p class="text-lg font-bold text-primary">
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
            v-if="showErrorDialog"
            :title="errorMessage"
            :show="showErrorDialog"
            @close="closeErrorDialog"
        />
    </div>
</template>

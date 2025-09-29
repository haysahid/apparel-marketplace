<script setup lang="ts">
import InputGroup from "@/Components/InputGroup.vue";
import CheckoutItem from "./CheckoutItem.vue";
import { computed, onMounted, ref } from "vue";
import DropdownSearchInput from "@/Components/DropdownSearchInput.vue";
import axios from "axios";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import InfoTooltip from "@/Components/InfoTooltip.vue";
import cookieManager from "@/plugins/cookie-manager";

const props = defineProps({
    cartGroup: {
        type: Object as () => CartGroupModel,
        required: true,
    },
});

const emit = defineEmits(["selectVoucher", "removeVoucher"]);

const selectedItems = computed(() => {
    return props.cartGroup.items?.filter((item) => item.selected) ?? [];
});

const vouchers = ref([]);
const searchVoucherCode = ref(null);
const filteredVouchers = computed(() => {
    if (!searchVoucherCode.value) return vouchers.value;

    return vouchers.value.filter((voucher) =>
        voucher.name
            .toLowerCase()
            .includes(searchVoucherCode.value.toLowerCase())
    );
});
const selectedVoucher = ref(null);
const voucherErrorMessage = ref(null);

function getStoreVouchers(storeId = null) {
    axios
        .get("/api/voucher", {
            params: {
                store_id: storeId,
            },
            headers: {
                authorization: `Bearer ${cookieManager.getItem(
                    "access_token"
                )}`,
            },
        })
        .then((response) => {
            vouchers.value = response.data.result;
        })
        .catch((error) => {
            voucherErrorMessage.value =
                error.response?.data?.message || "Gagal memuat voucher.";
        });
}

function checkVoucherCode(voucherCode: string, storeId = null) {
    axios
        .post(
            route("api.check-voucher"),
            {
                code: voucherCode,
                store_id: storeId,
            },
            {
                headers: {
                    authorization: `Bearer ${cookieManager.getItem(
                        "access_token"
                    )}`,
                },
            }
        )
        .then((response) => {
            selectedVoucher.value = response.data.result;
        })
        .catch((error) => {
            selectedVoucher.value = null;
            voucherErrorMessage.value =
                error.response?.data?.message || "Voucher tidak valid.";
        });
}

const showErrorDialog = ref(false);
const errorMessage = ref("");

function openErrorDialog(message: string) {
    errorMessage.value = message;
    showErrorDialog.value = true;
}

function closeErrorDialog() {
    showErrorDialog.value = false;
    errorMessage.value = "";
}

const sum = computed(() => {
    return selectedItems.value.reduce(
        (total, item) =>
            total + (item.variant?.final_selling_price ?? 0) * item.quantity,
        0
    );
});

const discount = computed(() => {
    if (!selectedVoucher.value) return 0;

    if (selectedVoucher.value.type === "percentage") {
        return Math.floor((sum.value * selectedVoucher.value.amount) / 100);
    } else {
        return Math.floor(selectedVoucher.value.amount);
    }
});

const shippingCost = computed(() => {
    return props.cartGroup.shipping?.cost ?? 0;
});

const totalPrice = computed(() => {
    return sum.value - discount.value + shippingCost.value;
});

onMounted(() => {
    getStoreVouchers(props.cartGroup.store_id);
});
</script>

<template>
    <div class="cart-group">
        <!-- Store Info -->
        <div class="flex items-center justify-between w-full gap-4">
            <div class="flex items-center gap-4">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    class="fill-gray-500 size-6"
                >
                    <path
                        d="M22 7.82001C22.006 7.75682 22.006 7.6932 22 7.63001L20 2.63001C19.9219 2.43237 19.7828 2.26475 19.603 2.15147C19.4232 2.03819 19.212 1.98514 19 2.00001H5C4.79972 1.99982 4.604 2.05977 4.43818 2.17209C4.27237 2.28442 4.1441 2.44395 4.07 2.63001L2.07 7.63001C2.06397 7.6932 2.06397 7.75682 2.07 7.82001C2.0371 7.87584 2.01346 7.93663 2 8.00001C2.01113 8.69125 2.20123 9.36781 2.55174 9.96369C2.90226 10.5596 3.40124 11.0544 4 11.4V21C4 21.2652 4.10536 21.5196 4.29289 21.7071C4.48043 21.8947 4.73478 22 5 22H19C19.2652 22 19.5196 21.8947 19.7071 21.7071C19.8946 21.5196 20 21.2652 20 21V11.44C20.6046 11.091 21.1072 10.5898 21.4581 9.98635C21.809 9.38287 21.9958 8.69807 22 8.00001C22.0091 7.94035 22.0091 7.87967 22 7.82001ZM13 20H11V16H13V20ZM18 20H15V15C15 14.7348 14.8946 14.4804 14.7071 14.2929C14.5196 14.1054 14.2652 14 14 14H10C9.73478 14 9.48043 14.1054 9.29289 14.2929C9.10536 14.4804 9 14.7348 9 15V20H6V12C6.56947 11.9968 7.13169 11.872 7.64905 11.634C8.16642 11.3961 8.627 11.0503 9 10.62C9.37537 11.0456 9.83701 11.3865 10.3542 11.62C10.8715 11.8535 11.4325 11.9743 12 11.9743C12.5675 11.9743 13.1285 11.8535 13.6458 11.62C14.163 11.3865 14.6246 11.0456 15 10.62C15.373 11.0503 15.8336 11.3961 16.3509 11.634C16.8683 11.872 17.4305 11.9968 18 12V20ZM18 10C17.4696 10 16.9609 9.7893 16.5858 9.41423C16.2107 9.03915 16 8.53044 16 8.00001C16 7.7348 15.8946 7.48044 15.7071 7.29291C15.5196 7.10537 15.2652 7.00001 15 7.00001C14.7348 7.00001 14.4804 7.10537 14.2929 7.29291C14.1054 7.48044 14 7.7348 14 8.00001C14 8.53044 13.7893 9.03915 13.4142 9.41423C13.0391 9.7893 12.5304 10 12 10C11.4696 10 10.9609 9.7893 10.5858 9.41423C10.2107 9.03915 10 8.53044 10 8.00001C10 7.7348 9.89464 7.48044 9.70711 7.29291C9.51957 7.10537 9.26522 7.00001 9 7.00001C8.73478 7.00001 8.48043 7.10537 8.29289 7.29291C8.10536 7.48044 8 7.7348 8 8.00001C8.00985 8.26266 7.96787 8.52467 7.87646 8.77109C7.78505 9.01751 7.646 9.24351 7.46725 9.43619C7.28849 9.62887 7.07354 9.78446 6.83466 9.89407C6.59578 10.0037 6.33764 10.0652 6.075 10.075C5.54457 10.0949 5.02796 9.90327 4.63882 9.54226C4.44614 9.36351 4.29055 9.14855 4.18094 8.90967C4.07133 8.67079 4.00985 8.41266 4 8.15001L5.68 4.00001H18.32L20 8.15001C19.9621 8.65403 19.7348 9.125 19.3637 9.46822C18.9927 9.81143 18.5054 10.0014 18 10Z"
                    />
                </svg>
                <div>
                    <h2 class="text-sm font-semibold">
                        {{ props.cartGroup.store?.name }}
                    </h2>
                    <p class="text-xs text-gray-500">
                        {{ props.cartGroup.store?.rajaongkir_origin_label }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div v-if="selectedItems?.length > 0" class="w-full">
            <CheckoutItem
                v-for="(item, index) in selectedItems"
                :key="index"
                :item="item"
                :showDivider="index !== selectedItems.length - 1"
            />
        </div>

        <!-- Divider -->
        <div
            v-if="props.cartGroup.items?.length > 0"
            class="w-full h-px bg-gray-200"
        ></div>

        <!-- Summary -->
        <div
            class="flex flex-col w-full mt-1 gap-y-2 gap-x-4 sm:mt-1.5 sm:flex-row sm:justify-between"
        >
            <!-- Voucher -->
            <div>
                <InputGroup
                    :id="`${props.cartGroup.store?.id}-voucher`"
                    label="Pilih Voucher Toko"
                >
                    <DropdownSearchInput
                        :id="`${props.cartGroup.store?.id}-voucher`"
                        :modelValue="
                            selectedVoucher
                                ? {
                                      label: `${selectedVoucher.name} - ${
                                          selectedVoucher.type === 'percentage'
                                              ? selectedVoucher.amount + '%'
                                              : selectedVoucher.amount
                                      }`,
                                      value: selectedVoucher.code,
                                  }
                                : null
                        "
                        :options="
                            filteredVouchers.map((voucher) => ({
                                label: `${voucher.name} - ${
                                    voucher.type === 'percentage'
                                        ? voucher.amount + '%'
                                        : voucher.amount
                                }`,
                                value: voucher.code,
                                disabled: voucher.is_redeemed ? true : false,
                            }))
                        "
                        placeholder="Pilih Voucher"
                        :disabled="$page.props.auth.user ? false : true"
                        @update:modelValue="
                            (option) => {
                                selectedVoucher = option
                                    ? vouchers.find(
                                          (voucher) =>
                                              voucher.code === option.value
                                      )
                                    : null;
                                if (selectedVoucher) {
                                    emit('selectVoucher', selectedVoucher);
                                } else {
                                    emit('removeVoucher');
                                }
                            }
                        "
                        @search="searchVoucherCode = $event"
                        @clear="
                            selectedVoucher = null;
                            searchVoucherCode = null;
                            voucherErrorMessage = null;
                            emit('removeVoucher');
                        "
                    />
                    <template #suffix>
                        <InfoTooltip
                            :id="`${props.cartGroup.store?.id}-voucher-hint`"
                        >
                            <template #content>
                                <p
                                    v-if="$page.props.auth.user"
                                    class="text-center text-wrap max-w-[320px]"
                                >
                                    Pilih voucher untuk mendapatkan potongan
                                    harga.
                                </p>
                                <p
                                    v-else
                                    class="text-center text-wrap max-w-[320px]"
                                >
                                    Silakan masuk untuk menggunakan voucher.
                                </p>
                            </template>
                        </InfoTooltip>
                    </template>
                </InputGroup>

                <p v-if="selectedVoucher" class="mt-1 text-xs text-green-600">
                    Voucher berhasil diterapkan.
                </p>
            </div>

            <!-- Summary -->
            <table class="text-sm">
                <tbody class="text-gray-800 [&>tr>td]:py-0.5">
                    <tr>
                        <td class="sm:text-end">Jumlah</td>
                        <td class="text-end">
                            {{ $formatCurrency(sum) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="sm:text-end">Voucher Diskon</td>
                        <td class="text-end">
                            - {{ $formatCurrency(discount) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="sm:text-end">Biaya Pengiriman</td>
                        <td class="text-end">
                            {{ $formatCurrency(shippingCost) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="font-semibold sm:text-end">Total Harga</td>
                        <td class="font-semibold text-end ps-8">
                            {{ $formatCurrency(totalPrice) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ErrorDialog :show="showErrorDialog" @close="showErrorDialog = false">
            <template #content>
                <div>
                    <div
                        class="mb-1 text-lg font-medium text-center text-gray-900"
                    >
                        Terjadi Kesalahan
                    </div>
                    <p class="text-center text-gray-700">
                        {{ errorMessage }}
                    </p>
                </div>
            </template>
        </ErrorDialog>
    </div>
</template>

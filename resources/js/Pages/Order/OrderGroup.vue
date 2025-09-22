<script setup lang="ts">
import Tooltip from "@/Components/Tooltip.vue";
import OrderItem from "./OrderItem.vue";
import { ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    orderGroup: {
        type: Object as () => OrderGroupModel,
        required: true,
    },
    showSummary: {
        type: Boolean,
        default: true,
    },
});

const isCopied = ref(false);
function copyToClipboard(text: string) {
    navigator.clipboard.writeText(text);

    isCopied.value = true;
    setTimeout(() => {
        isCopied.value = false;
    }, 2000);
}

const page = usePage();

const showDetailButton =
    route().current("order.success") ||
    route().current("order.success.guest") ||
    route().current("my-store.transaction.edit");

const detailLink = route().current("my-store.transaction.edit")
    ? route("my-store.order.edit", {
          invoice: props.orderGroup.invoice,
      })
    : route(
          page.props.auth.user ? "my-order.detail" : "my-order.detail.guest",
          props.orderGroup.invoice?.code
      );
</script>

<template>
    <div
        class="flex flex-col items-start justify-between gap-2 p-4 !pb-2 transition-all duration-200 ease-in-out rounded-2xl sm:p-6 sm:!pb-4 outline outline-1 -outline-offset-1 outline-gray-300 w-full"
    >
        <!-- Store Info -->
        <div class="flex items-center justify-between w-full gap-4">
            <div class="flex items-center gap-4">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    class="fill-gray-500 size-6 shrink-0"
                >
                    <path
                        d="M22 7.82001C22.006 7.75682 22.006 7.6932 22 7.63001L20 2.63001C19.9219 2.43237 19.7828 2.26475 19.603 2.15147C19.4232 2.03819 19.212 1.98514 19 2.00001H5C4.79972 1.99982 4.604 2.05977 4.43818 2.17209C4.27237 2.28442 4.1441 2.44395 4.07 2.63001L2.07 7.63001C2.06397 7.6932 2.06397 7.75682 2.07 7.82001C2.0371 7.87584 2.01346 7.93663 2 8.00001C2.01113 8.69125 2.20123 9.36781 2.55174 9.96369C2.90226 10.5596 3.40124 11.0544 4 11.4V21C4 21.2652 4.10536 21.5196 4.29289 21.7071C4.48043 21.8947 4.73478 22 5 22H19C19.2652 22 19.5196 21.8947 19.7071 21.7071C19.8946 21.5196 20 21.2652 20 21V11.44C20.6046 11.091 21.1072 10.5898 21.4581 9.98635C21.809 9.38287 21.9958 8.69807 22 8.00001C22.0091 7.94035 22.0091 7.87967 22 7.82001ZM13 20H11V16H13V20ZM18 20H15V15C15 14.7348 14.8946 14.4804 14.7071 14.2929C14.5196 14.1054 14.2652 14 14 14H10C9.73478 14 9.48043 14.1054 9.29289 14.2929C9.10536 14.4804 9 14.7348 9 15V20H6V12C6.56947 11.9968 7.13169 11.872 7.64905 11.634C8.16642 11.3961 8.627 11.0503 9 10.62C9.37537 11.0456 9.83701 11.3865 10.3542 11.62C10.8715 11.8535 11.4325 11.9743 12 11.9743C12.5675 11.9743 13.1285 11.8535 13.6458 11.62C14.163 11.3865 14.6246 11.0456 15 10.62C15.373 11.0503 15.8336 11.3961 16.3509 11.634C16.8683 11.872 17.4305 11.9968 18 12V20ZM18 10C17.4696 10 16.9609 9.7893 16.5858 9.41423C16.2107 9.03915 16 8.53044 16 8.00001C16 7.7348 15.8946 7.48044 15.7071 7.29291C15.5196 7.10537 15.2652 7.00001 15 7.00001C14.7348 7.00001 14.4804 7.10537 14.2929 7.29291C14.1054 7.48044 14 7.7348 14 8.00001C14 8.53044 13.7893 9.03915 13.4142 9.41423C13.0391 9.7893 12.5304 10 12 10C11.4696 10 10.9609 9.7893 10.5858 9.41423C10.2107 9.03915 10 8.53044 10 8.00001C10 7.7348 9.89464 7.48044 9.70711 7.29291C9.51957 7.10537 9.26522 7.00001 9 7.00001C8.73478 7.00001 8.48043 7.10537 8.29289 7.29291C8.10536 7.48044 8 7.7348 8 8.00001C8.00985 8.26266 7.96787 8.52467 7.87646 8.77109C7.78505 9.01751 7.646 9.24351 7.46725 9.43619C7.28849 9.62887 7.07354 9.78446 6.83466 9.89407C6.59578 10.0037 6.33764 10.0652 6.075 10.075C5.54457 10.0949 5.02796 9.90327 4.63882 9.54226C4.44614 9.36351 4.29055 9.14855 4.18094 8.90967C4.07133 8.67079 4.00985 8.41266 4 8.15001L5.68 4.00001H18.32L20 8.15001C19.9621 8.65403 19.7348 9.125 19.3637 9.46822C18.9927 9.81143 18.5054 10.0014 18 10Z"
                    />
                </svg>
                <div>
                    <h2 class="text-sm font-semibold">
                        {{ props.orderGroup.store?.name }}
                    </h2>
                    <p class="text-xs text-gray-500">
                        {{ props.orderGroup.store?.rajaongkir_origin_label }}
                    </p>
                </div>
            </div>

            <Link
                v-if="showDetailButton"
                :href="detailLink"
                :target="
                    route().current('my-store.transaction.edit')
                        ? undefined
                        : '_blank'
                "
            >
                <PrimaryButton type="button" @click="$event.stopPropagation()">
                    <template #prefix>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            class="size-5"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M19 21.5H6C5.07174 21.5 4.1815 21.1313 3.52513 20.4749C2.86875 19.8185 2.5 18.9283 2.5 18V4.94304C2.5 3.87604 3.556 3.19904 4.485 3.52104C4.61833 3.56704 4.74733 3.63437 4.872 3.72304L5.047 3.84804C5.47266 4.15007 5.98188 4.31189 6.5038 4.311C7.02572 4.3101 7.53438 4.14653 7.959 3.84304C8.55504 3.41886 9.26843 3.19092 10 3.19092C10.7316 3.19092 11.445 3.41886 12.041 3.84304C12.4656 4.14653 12.9743 4.3101 13.4962 4.311C14.0181 4.31189 14.5273 4.15007 14.953 3.84804L15.128 3.72304C16.121 3.01304 17.5 3.72304 17.5 4.94304V12.5H21C21.1989 12.5 21.3897 12.5791 21.5303 12.7197C21.671 12.8604 21.75 13.0511 21.75 13.25V18.75C21.75 19.4794 21.4603 20.1789 20.9445 20.6946C20.4288 21.2103 19.7293 21.5 19 21.5ZM17.75 14V18.75C17.75 19.0816 17.8817 19.3995 18.1161 19.6339C18.3505 19.8683 18.6685 20 19 20C19.3315 20 19.6495 19.8683 19.8839 19.6339C20.1183 19.3995 20.25 19.0816 20.25 18.75V14H17.75ZM13.5 9.75004C13.5 9.55113 13.421 9.36036 13.2803 9.21971C13.1397 9.07906 12.9489 9.00004 12.75 9.00004H6.75C6.55109 9.00004 6.36032 9.07906 6.21967 9.21971C6.07902 9.36036 6 9.55113 6 9.75004C6 9.94895 6.07902 10.1397 6.21967 10.2804C6.36032 10.421 6.55109 10.5 6.75 10.5H12.75C12.9489 10.5 13.1397 10.421 13.2803 10.2804C13.421 10.1397 13.5 9.94895 13.5 9.75004ZM12.5 12.75C12.5 12.5511 12.421 12.3604 12.2803 12.2197C12.1397 12.0791 11.9489 12 11.75 12H6.75C6.55109 12 6.36032 12.0791 6.21967 12.2197C6.07902 12.3604 6 12.5511 6 12.75C6 12.949 6.07902 13.1397 6.21967 13.2804C6.36032 13.421 6.55109 13.5 6.75 13.5H11.75C11.9489 13.5 12.1397 13.421 12.2803 13.2804C12.421 13.1397 12.5 12.949 12.5 12.75ZM12.75 15C12.9489 15 13.1397 15.0791 13.2803 15.2197C13.421 15.3604 13.5 15.5511 13.5 15.75C13.5 15.949 13.421 16.1397 13.2803 16.2804C13.1397 16.421 12.9489 16.5 12.75 16.5H6.75C6.55109 16.5 6.36032 16.421 6.21967 16.2804C6.07902 16.1397 6 15.949 6 15.75C6 15.5511 6.07902 15.3604 6.21967 15.2197C6.36032 15.0791 6.55109 15 6.75 15H12.75Z"
                                fill="currentColor"
                            />
                        </svg>
                    </template>
                    Detail
                </PrimaryButton>
            </Link>
        </div>

        <!-- Items -->
        <div v-if="props.orderGroup.items?.length > 0" class="w-full">
            <OrderItem
                v-for="(item, index) in props.orderGroup.items"
                :key="index"
                :item="item"
                :showDivider="index !== props.orderGroup.items.length - 1"
            />
        </div>

        <template v-if="props.showSummary">
            <!-- Divider -->
            <div
                v-if="props.orderGroup.items?.length > 0"
                class="w-full h-px my-1 bg-gray-200"
            ></div>

            <!-- Summary -->
            <div class="flex flex-col justify-between w-full mb-1 sm:flex-row">
                <!-- Invoice -->
                <div
                    class="items-center hidden gap-2 px-3 py-2 my-2 rounded-lg sm:flex bg-gray-50 h-fit sm:my-0"
                >
                    <div
                        class="flex sm:flex-col gap-x-0.5 items-center sm:items-start justify-between sm:justify-start w-full"
                    >
                        <p class="text-sm text-gray-700">No. Invoice</p>
                        <div class="flex items-center gap-0.5">
                            <p class="text-sm font-semibold text-gray-700">
                                {{ props.orderGroup.invoice?.code }}
                            </p>
                            <Tooltip
                                :id="`copy-code-tooltip-${props.orderGroup.invoice?.id}`"
                                placement="bottom"
                            >
                                <template #content>
                                    <p class="text-center min-w-[80px]">
                                        {{
                                            isCopied ? "Disalin!" : "Salin Kode"
                                        }}
                                    </p>
                                </template>

                                <button
                                    type="button"
                                    @click="
                                        copyToClipboard(
                                            props.orderGroup.invoice?.code
                                        )
                                    "
                                    class="p-1"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        class="text-gray-500 hover:text-gray-700 size-4"
                                    >
                                        <path
                                            d="M9 9V6.2C9 5.08 9 4.52 9.218 4.092C9.41 3.715 9.715 3.41 10.092 3.218C10.52 3 11.08 3 12.2 3H17.8C18.92 3 19.48 3 19.908 3.218C20.2843 3.40974 20.5903 3.71569 20.782 4.092C21 4.52 21 5.08 21 6.2V11.8C21 12.92 21 13.48 20.782 13.908C20.5903 14.2843 20.2843 14.5903 19.908 14.782C19.48 15 18.92 15 17.803 15H15M9 9H6.2C5.08 9 4.52 9 4.092 9.218C3.71569 9.40974 3.40974 9.71569 3.218 10.092C3 10.52 3 11.08 3 12.2V17.8C3 18.92 3 19.48 3.218 19.908C3.40974 20.2843 3.71569 20.5903 4.092 20.782C4.519 21 5.079 21 6.197 21H11.804C12.921 21 13.48 21 13.908 20.782C14.2843 20.5903 14.5903 20.2843 14.782 19.908C15 19.48 15 18.921 15 17.803V15M9 9H11.8C12.92 9 13.48 9 13.908 9.218C14.2843 9.40974 14.5903 9.71569 14.782 10.092C15 10.519 15 11.079 15 12.197V15"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </button>
                            </Tooltip>
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <table class="text-sm">
                    <tbody class="text-gray-800 [&>tr>td]:py-0.5">
                        <tr class="sm:hidden">
                            <td class="sm:text-end">No. Invoice</td>
                            <td class="text-end">
                                <div
                                    class="flex items-center justify-end gap-0.5"
                                >
                                    <p
                                        class="text-sm font-semibold text-gray-700"
                                    >
                                        {{ props.orderGroup.invoice?.code }}
                                    </p>
                                    <Tooltip
                                        :id="`copy-code-tooltip-table-${props.orderGroup.invoice?.id}`"
                                        placement="bottom"
                                    >
                                        <template #content>
                                            <p class="text-center min-w-[80px]">
                                                {{
                                                    isCopied
                                                        ? "Disalin!"
                                                        : "Salin Kode"
                                                }}
                                            </p>
                                        </template>

                                        <button
                                            type="button"
                                            @click="
                                                copyToClipboard(
                                                    props.orderGroup.invoice
                                                        ?.code
                                                )
                                            "
                                            class="p-1"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                class="text-gray-500 hover:text-gray-700 size-4"
                                            >
                                                <path
                                                    d="M9 9V6.2C9 5.08 9 4.52 9.218 4.092C9.41 3.715 9.715 3.41 10.092 3.218C10.52 3 11.08 3 12.2 3H17.8C18.92 3 19.48 3 19.908 3.218C20.2843 3.40974 20.5903 3.71569 20.782 4.092C21 4.52 21 5.08 21 6.2V11.8C21 12.92 21 13.48 20.782 13.908C20.5903 14.2843 20.2843 14.5903 19.908 14.782C19.48 15 18.92 15 17.803 15H15M9 9H6.2C5.08 9 4.52 9 4.092 9.218C3.71569 9.40974 3.40974 9.71569 3.218 10.092C3 10.52 3 11.08 3 12.2V17.8C3 18.92 3 19.48 3.218 19.908C3.40974 20.2843 3.71569 20.5903 4.092 20.782C4.519 21 5.079 21 6.197 21H11.804C12.921 21 13.48 21 13.908 20.782C14.2843 20.5903 14.5903 20.2843 14.782 19.908C15 19.48 15 18.921 15 17.803V15M9 9H11.8C12.92 9 13.48 9 13.908 9.218C14.2843 9.40974 14.5903 9.71569 14.782 10.092C15 10.519 15 11.079 15 12.197V15"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        </button>
                                    </Tooltip>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="sm:text-end">Jumlah</td>
                            <td class="text-end">
                                {{
                                    $formatCurrency(
                                        props.orderGroup.invoice?.base_amount ??
                                            0
                                    )
                                }}
                            </td>
                        </tr>
                        <tr>
                            <td class="sm:text-end">Voucher Diskon</td>
                            <td class="text-end">
                                -
                                {{
                                    $formatCurrency(
                                        props.orderGroup.invoice
                                            ?.voucher_amount ?? 0
                                    )
                                }}
                            </td>
                        </tr>
                        <tr>
                            <td class="sm:text-end">Biaya Pengiriman</td>
                            <td class="text-end">
                                {{
                                    $formatCurrency(
                                        props.orderGroup.invoice
                                            ?.shipping_cost ?? 0
                                    )
                                }}
                            </td>
                        </tr>
                        <tr>
                            <td class="font-semibold sm:text-end">
                                Total Harga
                            </td>
                            <td class="font-semibold text-end ps-8">
                                {{
                                    $formatCurrency(
                                        props.orderGroup.invoice?.amount ?? 0
                                    )
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>
</template>

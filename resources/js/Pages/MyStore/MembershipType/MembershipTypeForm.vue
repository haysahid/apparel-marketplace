<script setup lang="ts">
import { nextTick, onMounted, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputGroup from "@/Components/InputGroup.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { ColorPicker } from "vue3-colorpicker";
import "vue3-colorpicker/style.css";
import { useDialogStore } from "@/stores/dialog-store";
import { goBack } from "@/plugins/helpers";
import InfoTooltip from "@/Components/InfoTooltip.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";

const props = defineProps({
    membershipType: {
        type: Object,
        default: null,
    },
    isDialog: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["onSubmitted", "close"]);

const form = useForm(
    props.membershipType
        ? props.membershipType
        : {
              group: null,
              name: null,
              alias: null,
              level: 0,
              description: null,
              item_discount_percentage: 0,
              shipping_discount_percentage: 0,
              min_purchase_amount: 0,
              hex_code_bg: "#000000",
              hex_code_text: "#FFFFFF",
          }
);

const submit = () => {
    if (props.membershipType?.id) {
        form.transform((data) => {
            return {
                ...data,
                hex_code_bg: data.hex_code_bg?.toUpperCase(),
                hex_code_text: data.hex_code_text?.toUpperCase(),
            };
        }).post(
            route("my-store.membership-type.update", {
                membershipType: props.membershipType,
            }),
            {
                onError: (errors) => {
                    useDialogStore().openErrorDialog(errors.error);
                },
                onFinish: () => {
                    form.reset();
                },
            }
        );
    } else {
        form.transform((data) => {
            return {
                ...data,
                hex_code_bg: data.hex_code_bg?.toUpperCase(),
                hex_code_text: data.hex_code_text?.toUpperCase(),
                is_dialog: props.isDialog ? 1 : 0,
            };
        }).post(route("my-store.membership-type.store"), {
            preserveState: false,
            preserveScroll: false,
            onError: (errors) => {
                useDialogStore().openErrorDialog(errors.error);
            },
            onSuccess: () => {
                if (props.isDialog) emit("onSubmitted", form.name);
                form.reset();
            },
        });
    }
};

const bgColorPicker = ref(null);
const textColorPicker = ref(null);

onMounted(() => {
    nextTick(() => {
        const input = document.getElementById(
            "membership_type_name"
        ) as HTMLInputElement;
        input?.focus();
    });
});
</script>

<template>
    <form @submit.prevent="submit">
        <div class="flex flex-col items-start w-full gap-4">
            <div class="flex flex-col w-full gap-6">
                <!-- Fields -->
                <div class="flex flex-col w-full gap-4">
                    <div class="flex w-full gap-4">
                        <!-- Name -->
                        <InputGroup
                            for="membership-type-name"
                            label="Nama Jenis"
                            required
                        >
                            <TextInput
                                id="membership_type_name"
                                v-model="form.name"
                                type="text"
                                placeholder="Masukkan Nama Jenis"
                                class="block w-full"
                                required
                                :autofocus="true"
                                :error="form.errors.name"
                                @update:modelValue="form.errors.name = null"
                            />
                        </InputGroup>

                        <!-- Alias -->
                        <InputGroup for="membership_type_alias" label="Alias">
                            <TextInput
                                id="membership_type_alias"
                                v-model="form.alias"
                                type="text"
                                placeholder="Masukkan Alias"
                                class="block w-full"
                                :error="form.errors.alias"
                                @update:modelValue="form.errors.alias = null"
                            />

                            <template #suffix>
                                <InfoTooltip
                                    id="membership-type-alias-info"
                                    text="Alias digunakan untuk referensi internal."
                                />
                            </template>
                        </InputGroup>
                    </div>

                    <div class="flex w-full gap-4">
                        <!-- Group -->
                        <InputGroup
                            for="membership_type_group"
                            label="Grup"
                            required
                        >
                            <TextInput
                                id="membership_type_group"
                                v-model="form.group"
                                type="text"
                                placeholder="Contoh: Member / Reseller / Agen"
                                class="block w-full"
                                required
                                :error="form.errors.group"
                                @update:modelValue="form.errors.group = null"
                            />
                        </InputGroup>

                        <!-- Level -->
                        <InputGroup
                            for="membership_type_level"
                            label="Level VIP"
                            required
                        >
                            <TextInput
                                id="membership_type_level"
                                v-model.number="form.level"
                                type="number"
                                min="0"
                                placeholder="Masukkan Level"
                                class="block w-full"
                                required
                                :error="form.errors.level"
                                @update:modelValue="form.errors.level = null"
                            />

                            <template #suffix>
                                <InfoTooltip
                                    id="membership-type-level-info"
                                    text="Level VIP menentukan urutan keanggotaan. Level yang lebih tinggi menunjukkan keanggotaan yang lebih eksklusif."
                                />
                            </template>
                        </InputGroup>
                    </div>

                    <div class="flex flex-col w-full gap-4 sm:flex-row">
                        <!-- Item Discount Percentage -->
                        <InputGroup
                            for="item_discount_percentage"
                            label="Diskon Barang (%)"
                            required
                        >
                            <TextInput
                                id="item_discount_percentage"
                                v-model.number="form.item_discount_percentage"
                                type="number"
                                min="0"
                                max="100"
                                placeholder="Masukkan Diskon Barang (%)"
                                class="block w-full"
                                required
                                :error="form.errors.item_discount_percentage"
                                @update:modelValue="
                                    form.errors.item_discount_percentage = null
                                "
                            />

                            <template #suffix>
                                <InfoTooltip
                                    id="item-discount-percentage-info"
                                    text="Masukkan persentase diskon untuk barang yang dibeli oleh anggota dengan jenis keanggotaan ini."
                                />
                            </template>
                        </InputGroup>

                        <!-- Shipping Discount Percentage -->
                        <InputGroup
                            for="shipping_discount_percentage"
                            label="Diskon Pengiriman (%)"
                            required
                        >
                            <TextInput
                                id="shipping_discount_percentage"
                                v-model.number="
                                    form.shipping_discount_percentage
                                "
                                type="number"
                                min="0"
                                max="100"
                                placeholder="Masukkan Diskon Pengiriman (%)"
                                class="block w-full"
                                required
                                :error="
                                    form.errors.shipping_discount_percentage
                                "
                                @update:modelValue="
                                    form.errors.shipping_discount_percentage =
                                        null
                                "
                            />

                            <template #suffix>
                                <InfoTooltip
                                    id="shipping-discount-percentage-info"
                                    text="Masukkan persentase diskon untuk biaya pengiriman bagi anggota dengan jenis keanggotaan ini."
                                />
                            </template>
                        </InputGroup>
                    </div>

                    <!-- Min. Purchase Amount -->
                    <InputGroup
                        for="min_purchase_amount"
                        label="Jumlah Pembelian Minimum"
                        required
                    >
                        <TextInput
                            id="min_purchase_amount"
                            v-model.number="form.min_purchase_amount"
                            type="number"
                            min="0"
                            placeholder="Masukkan Jumlah Pembelian Minimum"
                            class="block w-full"
                            required
                            :error="form.errors.min_purchase_amount"
                            @update:modelValue="
                                form.errors.min_purchase_amount = null
                            "
                        />

                        <template #suffix>
                            <InfoTooltip
                                id="min-purchase-amount-info"
                                text="Masukkan jumlah pembelian minimum yang harus dicapai oleh anggota untuk memenuhi syarat jenis keanggotaan ini."
                            />
                        </template>
                    </InputGroup>

                    <!-- Description -->
                    <InputGroup
                        for="membership_type_description"
                        label="Deskripsi"
                    >
                        <TextAreaInput
                            id="membership_type_description"
                            v-model="form.description"
                            type="text"
                            placeholder="Masukkan Deskripsi"
                            class="block w-full"
                            :error="form.errors.description"
                            @update:modelValue="form.errors.description = null"
                        />
                    </InputGroup>

                    <div class="flex flex-col w-full gap-4 md:flex-row">
                        <div class="flex w-full gap-4">
                            <!-- Background Color -->
                            <InputGroup
                                for="hex_code_bg"
                                label="Background"
                                class="sm:!w-fit"
                            >
                                <button
                                    type="button"
                                    class="flex items-center p-1.5 border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-1 focus:border-primary-light focus:ring-primary-light w-fit"
                                    @click="bgColorPicker.showPicker = true"
                                >
                                    <ColorPicker
                                        ref="bgColorPicker"
                                        id="hex_code_bg"
                                        v-model:pureColor="form.hex_code_bg"
                                        format="hex"
                                        pickerType="chrome"
                                        :disableAlpha="true"
                                    />
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        class="w-6 h-6 fill-gray-600"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M20.4769 3.51094C20.1983 3.23231 19.8675 3.01129 19.5035 2.86049C19.1395 2.70969 18.7494 2.63208 18.3554 2.63208C17.9614 2.63208 17.5712 2.70969 17.2072 2.86049C16.8432 3.01129 16.5124 3.23231 16.2339 3.51094L14.7009 5.04394C14.1423 4.77547 13.5142 4.68718 12.9033 4.79126C12.2924 4.89534 11.729 5.18667 11.2909 5.62494L10.5779 6.33894C10.3921 6.52467 10.2447 6.74518 10.1441 6.98789C10.0436 7.23059 9.99179 7.49073 9.99179 7.75344C9.99179 8.01615 10.0436 8.27629 10.1441 8.51899C10.2447 8.7617 10.3921 8.98221 10.5779 9.16794L4.09187 15.6529C3.81333 15.9317 3.59243 16.2625 3.44178 16.6266C3.29114 16.9907 3.21369 17.3809 3.21387 17.7749V19.5749C3.21387 19.8932 3.3403 20.1984 3.56534 20.4235C3.79038 20.6485 4.09561 20.7749 4.41387 20.7749H6.21387C7.00925 20.7742 7.7718 20.4577 8.33387 19.8949L14.8199 13.4109C15.0056 13.5967 15.2261 13.7441 15.4688 13.8447C15.7115 13.9453 15.9717 13.997 16.2344 13.997C16.4971 13.997 16.7572 13.9453 16.9999 13.8447C17.2426 13.7441 17.4631 13.5967 17.6489 13.4109L18.3629 12.6959C18.8011 12.2578 19.0925 11.6944 19.1965 11.0835C19.3006 10.4726 19.2123 9.84446 18.9439 9.28594L20.4769 7.75394C20.7555 7.47536 20.9765 7.14462 21.1273 6.78061C21.2781 6.4166 21.3557 6.02645 21.3557 5.63244C21.3557 5.23843 21.2781 4.84828 21.1273 4.48427C20.9765 4.12026 20.7555 3.78952 20.4769 3.51094ZM5.50687 17.0679L11.9919 10.5819L13.4059 11.9959L6.92087 18.4819C6.73337 18.6695 6.47906 18.7749 6.21387 18.7749H5.21387V17.7749C5.21392 17.5097 5.31932 17.2554 5.50687 17.0679Z"
                                        />
                                    </svg>
                                </button>
                            </InputGroup>

                            <!-- Text Color -->
                            <InputGroup for="hex_code_text" label="Teks">
                                <button
                                    type="button"
                                    class="flex items-center p-1.5 border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-1 focus:border-primary-light focus:ring-primary-light w-fit"
                                    @click="textColorPicker.showPicker = true"
                                >
                                    <ColorPicker
                                        ref="textColorPicker"
                                        id="hex_code_text"
                                        v-model:pureColor="form.hex_code_text"
                                        format="hex"
                                        pickerType="chrome"
                                        :disableAlpha="true"
                                    />
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        class="w-6 h-6 fill-gray-600"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M20.4769 3.51094C20.1983 3.23231 19.8675 3.01129 19.5035 2.86049C19.1395 2.70969 18.7494 2.63208 18.3554 2.63208C17.9614 2.63208 17.5712 2.70969 17.2072 2.86049C16.8432 3.01129 16.5124 3.23231 16.2339 3.51094L14.7009 5.04394C14.1423 4.77547 13.5142 4.68718 12.9033 4.79126C12.2924 4.89534 11.729 5.18667 11.2909 5.62494L10.5779 6.33894C10.3921 6.52467 10.2447 6.74518 10.1441 6.98789C10.0436 7.23059 9.99179 7.49073 9.99179 7.75344C9.99179 8.01615 10.0436 8.27629 10.1441 8.51899C10.2447 8.7617 10.3921 8.98221 10.5779 9.16794L4.09187 15.6529C3.81333 15.9317 3.59243 16.2625 3.44178 16.6266C3.29114 16.9907 3.21369 17.3809 3.21387 17.7749V19.5749C3.21387 19.8932 3.3403 20.1984 3.56534 20.4235C3.79038 20.6485 4.09561 20.7749 4.41387 20.7749H6.21387C7.00925 20.7742 7.7718 20.4577 8.33387 19.8949L14.8199 13.4109C15.0056 13.5967 15.2261 13.7441 15.4688 13.8447C15.7115 13.9453 15.9717 13.997 16.2344 13.997C16.4971 13.997 16.7572 13.9453 16.9999 13.8447C17.2426 13.7441 17.4631 13.5967 17.6489 13.4109L18.3629 12.6959C18.8011 12.2578 19.0925 11.6944 19.1965 11.0835C19.3006 10.4726 19.2123 9.84446 18.9439 9.28594L20.4769 7.75394C20.7555 7.47536 20.9765 7.14462 21.1273 6.78061C21.2781 6.4166 21.3557 6.02645 21.3557 5.63244C21.3557 5.23843 21.2781 4.84828 21.1273 4.48427C20.9765 4.12026 20.7555 3.78952 20.4769 3.51094ZM5.50687 17.0679L11.9919 10.5819L13.4059 11.9959L6.92087 18.4819C6.73337 18.6695 6.47906 18.7749 6.21387 18.7749H5.21387V17.7749C5.21392 17.5097 5.31932 17.2554 5.50687 17.0679Z"
                                        />
                                    </svg>
                                </button>
                            </InputGroup>
                        </div>

                        <!-- Badge Preview -->
                        <InputGroup for="badge_design" label="Tampilan Badge">
                            <div class="flex gap-3">
                                <div
                                    class="flex items-center justify-center gap-1 px-3 py-2 text-sm font-semibold text-center rounded-full w-fit whitespace-nowrap h-fit"
                                    :style="{
                                        backgroundColor: form.hex_code_bg,
                                        color: form.hex_code_text,
                                    }"
                                >
                                    <span
                                        :class="{
                                            'opacity-50 italic': !form.name,
                                        }"
                                    >
                                        {{ form.name || "Jenis" }}
                                    </span>
                                    <span
                                        v-if="form.alias"
                                        class="text-xs opacity-80"
                                    >
                                        - {{ form.alias }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center justify-center gap-1 px-3 py-2 text-sm font-semibold text-center rounded-full w-fit whitespace-nowrap h-fit"
                                    :style="{
                                        backgroundColor: form.hex_code_bg,
                                        color: form.hex_code_text,
                                    }"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        class="inline-block size-5"
                                    >
                                        <path
                                            d="M4.04913 5.80188L7.85413 9.14988L11.2441 4.97588C11.3391 4.85919 11.4591 4.76542 11.5953 4.70155C11.7315 4.63768 11.8803 4.60536 12.0307 4.60699C12.1811 4.60863 12.3293 4.64417 12.464 4.71099C12.5988 4.7778 12.7168 4.87415 12.8091 4.99288L16.0441 9.14888L19.9721 5.75288C20.1264 5.61979 20.3176 5.53699 20.5202 5.51556C20.7228 5.49413 20.9271 5.5351 21.1058 5.63298C21.2845 5.73085 21.429 5.88097 21.52 6.06324C21.6111 6.24551 21.6442 6.45123 21.6151 6.65288L20.1151 16.9999H3.92213L2.39913 6.69988C2.36888 6.49711 2.40168 6.28995 2.49308 6.10645C2.58449 5.92294 2.73009 5.77197 2.91015 5.67397C3.09022 5.57597 3.29606 5.53568 3.49979 5.55856C3.70351 5.58144 3.89529 5.66638 4.04913 5.80188ZM4.00013 17.9999H20.0001V18.9999C20.0001 19.2651 19.8948 19.5194 19.7072 19.707C19.5197 19.8945 19.2654 19.9999 19.0001 19.9999H5.00013C4.73492 19.9999 4.48056 19.8945 4.29303 19.707C4.10549 19.5194 4.00013 19.2651 4.00013 18.9999V17.9999Z"
                                            fill="currentColor"
                                        />
                                    </svg>
                                    <span>VIP {{ form.level }}</span>
                                </div>
                            </div>
                        </InputGroup>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-4">
                <PrimaryButton type="submit"> Simpan </PrimaryButton>
                <SecondaryButton
                    v-if="!props.isDialog"
                    type="button"
                    @click="goBack()"
                >
                    Kembali
                </SecondaryButton>
                <SecondaryButton v-else type="button" @click="emit('close')">
                    Batalkan
                </SecondaryButton>
            </div>
        </div>
    </form>
</template>

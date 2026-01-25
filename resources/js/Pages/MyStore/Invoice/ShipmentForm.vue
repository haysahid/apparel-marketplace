<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputGroup from "@/Components/InputGroup.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { goBack } from "@/plugins/helpers";
import InfoTooltip from "@/Components/InfoTooltip.vue";
import invoiceService from "@/services/my-store/invoice-service";

const props = defineProps({
    invoice: {
        type: Object as () => InvoiceEntity,
        required: true,
    },
    items: {
        type: Array as () => TransactionItemEntity[],
        required: true,
    },
    isDialog: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close", "submitted"]);

const form = useForm({
    courier: null,
    tracking_number: null,
    weight: null,
    shipping_cost: null,
});

const selectedItems = props.items;

const submit = () => {
    invoiceService().setDelivering(
        {
            invoiceId: props.invoice.id,
            shipments: [
                {
                    invoice_id: props.invoice.id,
                    tracking_number: form.tracking_number,
                    courier_name: form.courier,
                    weight: Number(form.weight),
                    shipping_cost: Number(form.shipping_cost),
                    items: selectedItems.map((item) => {
                        return {
                            transaction_item_id: item.id,
                            shipped_quantity: item.quantity,
                        } as ShipmentItemEntity;
                    }),
                } as ShipmentEntity,
            ],
        },
        {
            autoShowDialog: true,
            onSuccess: () => {
                emit("submitted");
            },
        },
    );
};
</script>

<template>
    <form @submit.prevent="submit">
        <div class="flex flex-col items-start gap-4">
            <div class="flex flex-col w-full gap-4">
                <div class="flex flex-col gap-4">
                    <!-- Courier -->
                    <InputGroup for="courier" label="Kurir" required>
                        <TextInput
                            id="courier"
                            v-model="form.courier"
                            type="text"
                            placeholder="JNT, JNE, SiCepat, dll."
                            required
                            :autofocus="true"
                            :error="form.errors.courier"
                            @update:modelValue="form.errors.courier = null"
                        />
                    </InputGroup>

                    <!-- Tracking Number -->
                    <InputGroup
                        for="tracking_number"
                        label="Nomor Resi"
                        required
                    >
                        <TextInput
                            id="tracking_number"
                            v-model="form.tracking_number"
                            type="text"
                            placeholder="Masukkan Nomor Resi"
                            required
                            :error="form.errors.tracking_number"
                            @update:modelValue="
                                form.errors.tracking_number = null
                            "
                        />
                    </InputGroup>
                </div>

                <div class="flex flex-col gap-4">
                    <!-- Weight -->
                    <InputGroup for="weight" label="Berat (gram)" required>
                        <TextInput
                            id="weight"
                            v-model="form.weight"
                            type="number"
                            placeholder="Masukkan Berat (gram)"
                            required
                            :error="form.errors.weight"
                            @update:modelValue="form.errors.weight = null"
                        />

                        <template #suffix>
                            <InfoTooltip
                                id="weight-info"
                                text="Berat dalam satuan gram (g)"
                            />
                        </template>
                    </InputGroup>

                    <!-- Shipping Cost -->
                    <InputGroup
                        for="shipping_cost"
                        label="Biaya Pengiriman"
                        required
                    >
                        <TextInput
                            id="shipping_cost"
                            v-model="form.shipping_cost"
                            type="number"
                            placeholder="Masukkan Biaya Pengiriman"
                            :error="form.errors.shipping_cost"
                            @update:modelValue="
                                form.errors.shipping_cost = null
                            "
                        />
                    </InputGroup>
                </div>
            </div>

            <div
                class="flex items-center w-full gap-4 mt-4"
                :class="{
                    'justify-center': props.isDialog,
                }"
            >
                <PrimaryButton type="submit"> Simpan </PrimaryButton>
                <SecondaryButton
                    v-if="!isDialog"
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

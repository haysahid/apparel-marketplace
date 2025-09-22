import { ref, computed } from "vue";
import { defineStore } from "pinia";
import axios from "axios";

export const useMyStoreCartStore = defineStore("my_store_cart_group", () => {
    const key = "my_store_cart_group";
    const syncStatus = ref(null);

    const group = ref(
        localStorage.getItem(key)
            ? (JSON.parse(localStorage.getItem(key)) as CartGroupModel)
            : null
    );

    const items = computed(() => {
        return group.value?.items || [];
    });

    const subTotal = computed(() => {
        return items.value.reduce((total, item) => {
            const price = item.variant?.final_selling_price || 0;
            return total + price * item.quantity;
        }, 0);
    });

    const groupDiscount = computed(() => {
        if (!group.value?.voucher) return 0;

        const discount = group.value.voucher.amount || 0;
        if (group.value.voucher.type === "percentage") {
            return Math.floor((subTotal.value * discount) / 100);
        } else {
            return Math.floor(discount);
        }
    });

    const shippingCost = computed(() => {
        return group.value?.shipping.cost || 0;
    });

    function addItem(item: CartItemModel) {
        if (!group.value) {
            group.value = {
                store: item.store,
                items: [],
            } as CartGroupModel;
        }
        group.value.items.push(item);
        localStorage.setItem(key, JSON.stringify(group.value));
    }

    function updateItem(index: number, item: CartItemModel) {
        if (group.value) {
            group.value.items[index] = item;
            localStorage.setItem(key, JSON.stringify(group.value));
        }
    }

    function removeItem(index: number) {
        if (group.value) {
            group.value.items.splice(index, 1);
            if (group.value.items.length === 0) {
                group.value = null;
            }
            localStorage.removeItem(key);
        }
    }

    function syncCart() {
        if (!group.value) return;

        for (const item of items.value) {
            if (!group.value.store_id) {
                group.value.store_id = item.store_id;
            }
            if (item.store) delete item.store;
            if (item.variant) delete item.variant;
        }

        syncStatus.value = "loading";
        axios
            .post("/api/sync-cart", { cart_groups: [group.value] })
            .then((response) => {
                const updatedGroup = response.data.result[0];
                group.value = updatedGroup;
                localStorage.setItem(key, JSON.stringify(group.value));
                syncStatus.value = "success";
            })
            .catch((error) => {
                syncStatus.value = "error";
            });
    }

    return {
        group,
        items,
        subTotal,
        groupDiscount,
        shippingCost,
        addItem,
        updateItem,
        removeItem,
        syncCart,
        syncStatus,
    };
});

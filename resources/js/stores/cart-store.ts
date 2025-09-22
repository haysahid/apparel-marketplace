import { ref, computed } from "vue";
import { defineStore } from "pinia";
import axios from "axios";

export const useCartStore = defineStore("cart_groups", () => {
    const key = "cart_groups";

    const syncStatus = ref(null);

    const groups = ref(
        localStorage.getItem(key)
            ? (JSON.parse(localStorage.getItem(key)) as CartGroupModel[])
            : []
    );

    const items = computed(() => {
        return groups.value.reduce((acc, group) => {
            return acc.concat(group.items);
        }, [] as CartItemModel[]);
    });

    const selectedGroups = computed(() => {
        return groups.value.filter((item) => item.selected) as CartGroupModel[];
    });

    const groupHasSelectedItems = computed(() => {
        return groups.value.filter((group) =>
            group.items?.some((item) => item.selected)
        );
    });

    const selectedItems = computed(() => {
        return groups.value.reduce((acc, group) => {
            const selectedGroupItems =
                group.items?.filter((item) => item.selected) || [];
            return acc.concat(selectedGroupItems);
        }, [] as CartItemModel[]);
    });

    const subTotal = computed(() => {
        return selectedItems.value.reduce((total, item) => {
            const price = item.variant?.final_selling_price || 0;
            return total + price * item.quantity;
        }, 0);
    });

    const totalGroupDiscount = computed(() => {
        return Math.floor(
            groupHasSelectedItems.value.reduce((total, group) => {
                if (!group.voucher) return total;

                const discount = group.voucher.amount || 0;
                if (group.voucher.type === "percentage") {
                    const groupTotal = group.items
                        .filter((item) => item.selected)
                        .reduce((sum, item) => {
                            const price =
                                item.variant?.final_selling_price || 0;
                            return sum + price * item.quantity;
                        }, 0);
                    return total + (groupTotal * discount) / 100;
                } else {
                    return total + discount;
                }
            }, 0)
        );
    });

    const totalShippingCost = computed(() => {
        return groupHasSelectedItems.value.reduce((total, group) => {
            const shippingCost = group.shipping?.cost || 0;
            return total + shippingCost;
        }, 0);
    });

    const toggleGroup = (group: CartGroupModel) => {
        const index = groups.value.findIndex(
            (i) => i.store_id === group.store_id
        );
        if (index === -1) return;

        groups.value[index].selected = !groups.value[index].selected;
        groups.value[index].items.forEach((i) => {
            i.selected = groups.value[index].selected;
        });

        localStorage.setItem(key, JSON.stringify(groups.value));
    };

    const toggleItem = (item: CartItemModel) => {
        const groupIndex = groups.value.findIndex(
            (g) => g.store_id === item.store_id
        );
        if (groupIndex === -1) return;

        const itemIndex = groups.value[groupIndex].items.findIndex(
            (i) => i.variant_id === item.variant_id
        );
        if (itemIndex === -1) return;

        groups.value[groupIndex].items[itemIndex].selected =
            !groups.value[groupIndex].items[itemIndex].selected;

        if (groups.value[groupIndex].items.every((i) => i.selected)) {
            groups.value[groupIndex].selected = true;
        } else {
            groups.value[groupIndex].selected = false;
        }

        localStorage.setItem(key, JSON.stringify(groups.value));
    };

    const addGroup = (group: CartGroupModel) => {
        // Check if the item already exists in the cart
        const existingGroupIndex = groups.value.findIndex(
            (g) => g.store_id === group.store_id
        );

        if (existingGroupIndex !== -1) {
            // If it exists, update the items array
            groups.value[existingGroupIndex].items = [
                ...groups.value[existingGroupIndex].items,
                ...group.items,
            ];
        } else {
            // If it doesn't exist, add the new item
            groups.value.unshift(group);
        }

        localStorage.setItem(key, JSON.stringify(groups.value));
    };

    const updateItem = (item: CartItemModel) => {
        const groupIndex = groups.value.findIndex(
            (g) => g.store_id === item.store_id
        );
        if (groupIndex === -1) return;

        const itemIndex = groups.value[groupIndex].items.findIndex(
            (i) => i.variant_id === item.variant_id
        );
        if (itemIndex !== -1) {
            groups.value[groupIndex].items[itemIndex] = item;
        }
        localStorage.setItem(key, JSON.stringify(groups.value));
    };

    const removeItem = (item: CartItemModel) => {
        const groupIndex = groups.value.findIndex(
            (g) => g.store_id === item.store_id
        );
        if (groupIndex === -1) return;

        groups.value[groupIndex].items =
            groups.value[groupIndex].items?.filter(
                (i) => i.variant_id !== item.variant_id
            ) || [];

        // If the items array is empty, remove the group
        if (groups.value[groupIndex].items?.length === 0) {
            groups.value.splice(groupIndex, 1);
        }

        localStorage.setItem(key, JSON.stringify(groups.value));
    };

    const clearCart = () => {
        groups.value = [];
        localStorage.setItem(key, JSON.stringify(groups.value));
    };

    const getItemByVariant = (variant: ProductVariantEntity): CartItemModel => {
        const groupIndex = groups.value.findIndex(
            (g) => g.store_id === variant?.store_id
        );
        if (groupIndex === -1) return null;

        const foundItem = groups.value[groupIndex].items.find(
            (g) => g.variant_id === variant?.id
        );
        if (foundItem) return foundItem;
        return null;
    };

    const updateGroup = (group: CartGroupModel) => {
        const index = groups.value.findIndex(
            (i) => i.store_id === group.store_id
        );
        if (index === -1) return;

        groups.value[index] = group;
        localStorage.setItem(key, JSON.stringify(groups.value));
    };

    const updateGroups = (updatedGroups: CartGroupModel[]) => {
        updatedGroups.forEach((updatedGroup) => {
            const index = groups.value.findIndex(
                (g) => g.store_id === updatedGroup.store_id
            );
            if (index !== -1) {
                groups.value[index] = updatedGroup;
            }
        });

        localStorage.setItem(key, JSON.stringify(groups.value));
    };

    const updateAllGroups = (newGroups: CartGroupModel[]) => {
        groups.value = newGroups;
        localStorage.setItem(key, JSON.stringify(groups.value));
    };

    const removeSelectedItems = () => {
        groups.value = groups.value.filter((group) => !group.selected);

        groups.value.forEach((group) => {
            group.items = group.items.filter((item) => !item.selected);
        });

        localStorage.setItem(key, JSON.stringify(groups.value));
    };

    const syncCart = () => {
        if (groups.value.length === 0) return;

        for (const group of groups.value) {
            for (const item of group.items) {
                if (!group.store_id) {
                    group.store_id = item.store_id;
                }
                if (item.store) delete item.store;
                if (item.variant) delete item.variant;
            }
        }

        syncStatus.value = "loading";
        axios
            .post("/api/sync-cart", { cart_groups: groups.value })
            .then((response) => {
                const updatedGroups = response.data.result;
                groups.value = updatedGroups;
                localStorage.setItem(key, JSON.stringify(groups.value));
                syncStatus.value = "success";
            })
            .catch((error) => {
                syncStatus.value = "error";
            });
    };

    return {
        groups,
        items,
        selectedGroups,
        groupHasSelectedItems,
        selectedItems,
        subTotal,
        totalGroupDiscount,
        totalShippingCost,
        toggleGroup,
        toggleItem,
        addGroup,
        updateItem,
        removeItem,
        clearCart,
        getItemByVariant,
        updateGroup,
        updateGroups,
        updateAllGroups,
        removeSelectedItems,
        syncStatus,
        syncCart,
    };
});

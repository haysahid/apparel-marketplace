import { ref } from "vue";
import { defineStore } from "pinia";

export const useMyStoreStore = defineStore("my_store", () => {
    const selectedStoreId = ref(
        localStorage.getItem("selected_store_id") || null
    );

    const setSelectedStoreId = (storeId: string | null) => {
        selectedStoreId.value = storeId;
        localStorage.setItem("selected_store_id", storeId || "");
    };

    return {
        selectedStoreId,
        setSelectedStoreId,
    };
});

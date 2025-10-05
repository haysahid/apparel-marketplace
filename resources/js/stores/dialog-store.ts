import { defineStore } from "pinia";
import { ref } from "vue";

export const useDialogStore = defineStore("dialog", () => {
    const showSuccessDialog = ref(false);
    const successMessage = ref(null);

    const showErrorDialog = ref(false);
    const errorMessage = ref(null);

    function openSuccessDialog(message: string) {
        successMessage.value = message;
        showSuccessDialog.value = true;
    }

    function openErrorDialog(message: string) {
        errorMessage.value = message;
        showErrorDialog.value = true;
    }

    return {
        showSuccessDialog,
        successMessage,
        openSuccessDialog,
        showErrorDialog,
        errorMessage,
        openErrorDialog,
    };
});

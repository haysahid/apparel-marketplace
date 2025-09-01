import { ref } from "vue";
import { defineStore } from "pinia";

export const useOrderStore = defineStore("order", () => {
    const form = ref(
        localStorage.getItem("order_form")
            ? (JSON.parse(
                  localStorage.getItem("order_form")
              ) as OrderDetailFormModel)
            : ({} as OrderDetailFormModel)
    );

    const updateForm = (updatedForm: OrderDetailFormModel) => {
        form.value = updatedForm;
        localStorage.setItem("order_form", JSON.stringify(updatedForm));
    };

    const clearForm = () => {
        form.value = {} as OrderDetailFormModel;
        localStorage.setItem("order_form", JSON.stringify(form.value));
    };

    return {
        form,
        updateForm,
        clearForm,
    };
});

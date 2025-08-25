<script setup>
import { computed, ref } from "vue";
import Checkbox from "@/Components/Checkbox.vue";
import DialogModal from "@/Components/DialogModal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    show: Boolean,
    categories: Array,
});

const emit = defineEmits(["close", "change"]);

const categorySearch = ref("");
const filteredCategories = computed(() => {
    if (categorySearch.value === "") {
        return props.categories;
    }

    return props.categories.filter((category) =>
        category.name.toLowerCase().includes(categorySearch.value.toLowerCase())
    );
});
</script>

<template>
    <DialogModal
        :show="props.show"
        containerClass="!p-0"
        @close="emit('close')"
    >
        <template #content>
            <div class="flex items-center w-full gap-4 px-6 pt-6 pb-2">
                <h3 class="w-full text-base font-bold text-gray-800 text-start">
                    Pilih Kategori
                </h3>
                <TextInput
                    id="category-search"
                    v-model="categorySearch"
                    type="text"
                    placeholder="Cari kategori..."
                    class="w-full max-w-48"
                >
                    <template #suffix>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            class="absolute -translate-y-1/2 fill-gray-400 right-3 top-1/2 size-5"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M11 17C11.7879 17 12.5681 16.8448 13.2961 16.5433C14.0241 16.2417 14.6855 15.7998 15.2426 15.2426C15.7998 14.6855 16.2417 14.0241 16.5433 13.2961C16.8448 12.5681 17 11.7879 17 11C17 10.2121 16.8448 9.43185 16.5433 8.7039C16.2417 7.97595 15.7998 7.31451 15.2426 6.75736C14.6855 6.20021 14.0241 5.75825 13.2961 5.45672C12.5681 5.15519 11.7879 5 11 5C9.4087 5 7.88258 5.63214 6.75736 6.75736C5.63214 7.88258 5 9.4087 5 11C5 12.5913 5.63214 14.1174 6.75736 15.2426C7.88258 16.3679 9.4087 17 11 17ZM11 19C13.1217 19 15.1566 18.1571 16.6569 16.6569C18.1571 15.1566 19 13.1217 19 11C19 8.87827 18.1571 6.84344 16.6569 5.34315C15.1566 3.84285 13.1217 3 11 3C8.87827 3 6.84344 3.84285 5.34315 5.34315C3.84285 6.84344 3 8.87827 3 11C3 13.1217 3.84285 15.1566 5.34315 16.6569C6.84344 18.1571 8.87827 19 11 19Z"
                            />
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M15.3201 15.2903C15.5082 15.1035 15.7629 14.9991 16.0281 15C16.2933 15.0009 16.5472 15.1072 16.7341 15.2953L20.7091 19.2953C20.8908 19.4844 20.9909 19.7373 20.9879 19.9995C20.9849 20.2618 20.879 20.5123 20.6931 20.6972C20.5071 20.8822 20.256 20.9866 19.9937 20.9881C19.7315 20.9896 19.4791 20.8881 19.2911 20.7053L15.3161 16.7053C15.1291 16.5172 15.0245 16.2626 15.0253 15.9975C15.026 15.7323 15.1321 15.4783 15.3201 15.2913V15.2903Z"
                            />
                        </svg>
                    </template>
                </TextInput>
            </div>

            <div class="min-h-32 max-h-[60vh] overflow-y-auto w-full px-6 py-2">
                <div
                    v-if="filteredCategories.length > 0"
                    class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2"
                >
                    <label
                        v-for="category in filteredCategories || []"
                        :key="category.id"
                        :for="`category-${category.id}`"
                        class="flex items-center gap-2 cursor-pointer [&>*]:cursor-pointer justify-start"
                    >
                        <Checkbox
                            :id="`category-${category.id}`"
                            :label="category.name"
                            :checked="category.selected"
                            @update:checked="
                                category.selected
                                    ? (category.selected = false)
                                    : (category.selected = true);

                                emit('change', props.categories);
                            "
                        />
                        <InputLabel
                            :for="`category-${category.id}`"
                            :value="category.name"
                            class="text-sm text-gray-500"
                        />
                    </label>
                </div>
                <div
                    v-else
                    class="flex items-center justify-center w-full h-24 text-sm text-gray-500"
                >
                    Tidak ada kategori yang ditemukan.
                </div>
            </div>
            <div class="px-6 pt-2 pb-6 bg-white">
                <PrimaryButton
                    type="button"
                    @click="
                        categorySearch = '';
                        emit('close');
                    "
                >
                    Tutup
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
</template>

<script setup>
import { computed } from "vue";

const emit = defineEmits(["update:checked"]);

const props = defineProps({
    id: {
        type: String,
        default: null,
    },
    name: {
        type: String,
        default: null,
    },
    checked: {
        type: [Array, Boolean],
        default: false,
    },
    value: {
        type: String,
        default: null,
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit("update:checked", val);
    },
});
</script>

<template>
    <input
        :id="id"
        :name="name"
        v-model="proxyChecked"
        type="checkbox"
        :value="value"
        class="border-gray-400 rounded shadow-sm text-primary focus:ring-primary focus:border-primary focus:ring-2 focus:ring-offset-2"
    />
</template>

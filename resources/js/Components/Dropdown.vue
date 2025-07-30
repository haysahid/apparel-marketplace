<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from "vue";

const props = defineProps({
    align: {
        type: String,
        default: "right",
    },
    width: {
        type: String,
        default: "48",
    },
    contentClasses: {
        type: Array,
        default: () => ["py-1", "bg-white"],
    },
    showBackdrop: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["onOpen", "onClose"]);

let open = ref(false);

const closeOnEscape = (e) => {
    if (open.value && e.key === "Escape") {
        open.value = false;
    }
};

onMounted(() => document.addEventListener("keydown", closeOnEscape));
onUnmounted(() => document.removeEventListener("keydown", closeOnEscape));

watch(open, (newValue) => {
    if (newValue) {
        emit("onOpen");
    } else {
        emit("onClose");
    }
});

const widthClass = computed(() => {
    return {
        32: "!w-32",
        48: "!w-48",
        52: "!w-52",
        56: "!w-56",
        64: "!w-64",
        full: "!w-full",
    }[props.width.toString()];
});

const alignmentClasses = computed(() => {
    if (props.align === "left") {
        return "ltr:origin-top-left rtl:origin-top-right start-0";
    }

    if (props.align === "right") {
        return "ltr:origin-top-right rtl:origin-top-left end-0";
    }

    return "origin-top";
});

defineExpose({
    open,
});
</script>

<template>
    <div class="relative">
        <div @click="open = !open">
            <slot name="trigger" />
        </div>

        <!-- Full Screen Dropdown Overlay -->
        <div v-show="open" class="fixed inset-0 z-40" @click="open = false">
            <transition
                v-if="showBackdrop"
                enter-active-class="duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-show="open"
                    class="fixed inset-0 transition-all transform"
                    @click="open = false"
                >
                    <div class="absolute inset-0 bg-gray-500 opacity-15" />
                </div>
            </transition>
        </div>

        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <div
                v-show="open"
                class="absolute z-[999] w-full mt-2 rounded-md shadow-lg"
                :class="[widthClass, alignmentClasses]"
                style="display: none"
                @click="open = false"
            >
                <div
                    class="rounded-md ring-1 ring-black ring-opacity-5"
                    :class="contentClasses"
                >
                    <slot name="content" />
                </div>
            </div>
        </transition>
    </div>
</template>

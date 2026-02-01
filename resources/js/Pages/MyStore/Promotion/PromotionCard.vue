<script setup lang="ts">
import AdminItemAction from "@/Components/AdminItemAction.vue";
import { getImageUrl } from "@/plugins/helpers";
import { computed, getCurrentInstance } from "vue";

const props = defineProps({
    promotion: {
        required: true,
        type: Object as () => PromotionEntity,
    },
    selected: {
        type: Boolean,
        default: false,
    },
    hideEditButton: {
        type: Boolean,
        default: false,
    },
    hideDeleteButton: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["edit", "delete"]);

const hasEditCallback = computed(() => {
    return !!getCurrentInstance()?.vnode?.props?.["onEdit"];
});
const hasDeleteCallback = computed(() => {
    return !!getCurrentInstance()?.vnode?.props?.["onDelete"];
});
</script>

<template>
    <div
        class="relative flex items-center justify-start gap-3 p-2 border rounded-lg cursor-pointer sm:gap-4 min-h-14"
        :class="
            props.selected
                ? 'border-primary bg-primary/5 outline outline-2 outline-primary -outline-offset-2'
                : 'border-gray-200 hover:bg-gray-50'
        "
    >
        <img
            v-if="props.promotion.image"
            :src="getImageUrl(props.promotion.image)"
            :alt="props.promotion.name"
            class="object-contain w-16 aspect-square"
        />
        <div v-else class="flex items-center justify-center w-16 aspect-square">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                class="size-6 sm:size-8 fill-gray-300"
                :class="props.selected ? 'fill-primary' : 'fill-gray-300'"
            >
                <path
                    d="M5.5 7C5.10218 7 4.72064 6.84196 4.43934 6.56066C4.15804 6.27936 4 5.89782 4 5.5C4 5.10218 4.15804 4.72064 4.43934 4.43934C4.72064 4.15804 5.10218 4 5.5 4C5.89782 4 6.27936 4.15804 6.56066 4.43934C6.84196 4.72064 7 5.10218 7 5.5C7 5.89782 6.84196 6.27936 6.56066 6.56066C6.27936 6.84196 5.89782 7 5.5 7ZM21.41 11.58L12.41 2.58C12.05 2.22 11.55 2 11 2H4C2.89 2 2 2.89 2 4V11C2 11.55 2.22 12.05 2.59 12.41L11.58 21.41C11.95 21.77 12.45 22 13 22C13.55 22 14.05 21.77 14.41 21.41L21.41 14.41C21.78 14.05 22 13.55 22 13C22 12.44 21.77 11.94 21.41 11.58Z"
                />
            </svg>
        </div>
        <div class="flex flex-col items-start justify-center">
            <p class="text-sm font-medium text-gray-700">
                {{ props.promotion.name }}
            </p>
            <p v-if="props.promotion.end_date" class="text-xs text-gray-500">
                Berlaku hingga {{ $formatDate(props.promotion.end_date) }}
            </p>
        </div>

        <AdminItemAction
            v-if="
                (hasEditCallback && !props.hideEditButton) ||
                (hasDeleteCallback && !props.hideDeleteButton)
            "
            class="absolute top-2.5 right-2.5 sm:top-4 sm:right-4"
            :hideEditButton="props.hideEditButton"
            :hideDeleteButton="props.hideDeleteButton"
            @edit="emit('edit')"
            @delete="emit('delete')"
        />
    </div>
</template>

<script setup>
import Chip from "./Chip.vue";
import ColorChip from "./ColorChip.vue";
import InputGroup from "./InputGroup.vue";

const props = defineProps({
    title: {
        type: String,
        default: null,
    },
    motifs: {
        type: Array,
        default: () => [],
    },
    colors: {
        type: Array,
        default: () => [],
    },
    selectedMotif: {
        type: String,
        default: null,
    },
    selectedColor: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["update:selectedMotif", "update:selectedColor"]);
</script>

<template>
    <div class="flex flex-col gap-2">
        <div v-if="props.title" class="flex items-center justify-between gap-2">
            <h3 class="text-sm font-semibold">
                {{ props.title }}
            </h3>
            <button
                v-if="props.selectedMotif || props.selectedColor"
                type="button"
                class="text-sm text-red-700 hover:underline w-fit"
                @click="
                    emit('update:selectedMotif', null);
                    emit('update:selectedColor', null);
                "
            >
                Hapus Filter
            </button>
        </div>
        <button
            v-else-if="props.selectedMotif || props.selectedColor"
            type="button"
            class="text-sm text-red-700 hover:underline w-fit"
            @click="
                emit('update:selectedMotif', null);
                emit('update:selectedColor', null);
            "
        >
            Hapus Filter
        </button>
        <InputGroup label="Motif">
            <div class="flex flex-wrap gap-2">
                <Chip
                    v-for="(motif, index) in props.motifs"
                    :key="index"
                    :label="motif"
                    :selected="props.selectedMotif == motif"
                    @click="
                        if (props.selectedMotif == motif) {
                            emit('update:selectedMotif', null);
                        } else {
                            emit('update:selectedMotif', motif);
                        }
                    "
                />
            </div>
        </InputGroup>
        <InputGroup label="Warna">
            <div class="flex flex-wrap gap-2">
                <ColorChip
                    v-for="(color, index) in props.colors"
                    :key="index"
                    :label="color.name"
                    :hexCode="color.hex_code"
                    :selected="props.selectedColor?.id == color.id"
                    @click="
                        if (props.selectedColor?.id == color.id) {
                            emit('update:selectedColor', null);
                        } else {
                            emit('update:selectedColor', color);
                        }
                    "
                />
            </div>
        </InputGroup>
    </div>
</template>

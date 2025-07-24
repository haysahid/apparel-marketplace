<script setup>
const props = defineProps({
    length: {
        type: Number,
        default: 0,
    },
    showBackground: {
        type: Boolean,
        default: true,
    },
    invert: {
        type: Boolean,
        default: false,
    },
    active: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["click"]);
</script>

<template>
    <button
        type="button"
        class="relative flex items-center justify-center p-2 transition duration-150 ease-in-out rounded-md text-gray-500/80 hover:text-gray-500 hover:bg-gray-500/10 focus:outline-none focus:bg-gray-500/10 focus:hover:bg-gray-500/20 focus:text-gray-500 group"
        :class="{
            '!bg-transparent !p-0 hover:!bg-transparent': !showBackground,
            'hover:bg-white/20 focus:bg-white/10': showBackground && invert,
            'bg-primary/10 hover:!bg-primary/10 focus:bg-primary/20':
                showBackground && active,
        }"
        @click="emit('click')"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="25"
            viewBox="0 0 24 25"
            class="fill-gray-500/80 group-hover:fill-gray-500"
            :class="{
                'group-hover:fill-gray-500/80': !showBackground,
                'fill-white/80 group-hover:fill-white/80':
                    showBackground && invert,
                'fill-primary group-hover:fill-primary': active,
            }"
        >
            <path
                d="M17 18.5422C17.5304 18.5422 18.0391 18.753 18.4142 19.128C18.7893 19.5031 19 20.0118 19 20.5422C19 21.0727 18.7893 21.5814 18.4142 21.9564C18.0391 22.3315 17.5304 22.5422 17 22.5422C16.4696 22.5422 15.9609 22.3315 15.5858 21.9564C15.2107 21.5814 15 21.0727 15 20.5422C15 19.4322 15.89 18.5422 17 18.5422ZM1 2.54224H4.27L5.21 4.54224H20C20.2652 4.54224 20.5196 4.64759 20.7071 4.83513C20.8946 5.02267 21 5.27702 21 5.54224C21 5.71224 20.95 5.88224 20.88 6.04224L17.3 12.5122C16.96 13.1222 16.3 13.5422 15.55 13.5422H8.1L7.2 15.1722L7.17 15.2922C7.17 15.3585 7.19634 15.4221 7.24322 15.469C7.29011 15.5159 7.3537 15.5422 7.42 15.5422H19V17.5422H7C6.46957 17.5422 5.96086 17.3315 5.58579 16.9564C5.21071 16.5814 5 16.0727 5 15.5422C5 15.1922 5.09 14.8622 5.24 14.5822L6.6 12.1322L3 4.54224H1V2.54224ZM7 18.5422C7.53043 18.5422 8.03914 18.753 8.41421 19.128C8.78929 19.5031 9 20.0118 9 20.5422C9 21.0727 8.78929 21.5814 8.41421 21.9564C8.03914 22.3315 7.53043 22.5422 7 22.5422C6.46957 22.5422 5.96086 22.3315 5.58579 21.9564C5.21071 21.5814 5 21.0727 5 20.5422C5 19.4322 5.89 18.5422 7 18.5422ZM16 11.5422L18.78 6.54224H6.14L8.5 11.5422H16Z"
            />
        </svg>
        <div
            v-if="length > 0"
            class="px-1 py-0.5 ms-2 text-xs font-semibold text-white bg-primary rounded-full min-w-[20px] text-center absolute top-0 right-0"
            :class="{
                'bg-primary/80': !showBackground,
                'bg-primary': showBackground && invert,
            }"
        >
            {{ length }}
        </div>
    </button>
</template>

import { ref, computed, onMounted, onUnmounted } from 'vue';

export function useScreenSize() {
    const width = ref(window.innerWidth);
    const height = ref(window.innerHeight);

    const updateSize = () => {
        width.value = window.innerWidth;
        height.value = window.innerHeight;
    };

    const is = (size) => {
        const sizes = {
            xs: 0,
            sm: 640,
            md: 768,
            lg: 1024,
            xl: 1280,
            '2xl': 1536,
        };
        return width.value >= sizes[size];
    };

    onMounted(() => {
        window.addEventListener('resize', updateSize);
    });

    onUnmounted(() => {
        window.removeEventListener('resize', updateSize);
    });

    return { width, height, is };
}
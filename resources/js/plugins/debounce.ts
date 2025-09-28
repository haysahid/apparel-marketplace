import { ref } from "vue";

export default function useDebounce() {
    const timeout = ref(null);

    function debounce(func: () => void, wait: number) {
        if (timeout.value) {
            clearTimeout(timeout.value);
        }
        timeout.value = setTimeout(() => {
            func();
        }, wait);
    }

    return debounce;
}

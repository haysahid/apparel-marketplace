<script setup>
const props = defineProps({
    title: {
        type: String,
        default: "Pesanan Berhasil Dibuat!",
    },
    subtitle: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <div class="relative flex items-center justify-center w-full py-3 sm:p-12">
        <!-- Success Card -->
        <div class="relative w-full max-w-md text-center success-card">
            <div
                class="flex items-center justify-center w-20 h-20 mx-auto mb-8 rounded-full shadow-lg success-icon bg-gradient-to-br from-green-400 to-green-500"
            >
                <svg
                    class="checkmark w-15 h-15 text-white stroke-[3]"
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    viewBox="0 0 24 24"
                >
                    <path class="checkmark-path" d="M9 12l2 2 4-4" />
                </svg>
            </div>

            <h1
                class="mb-4 text-2xl font-bold text-green-500 sm:text-3xl title bg-clip-text"
            >
                {{ props.title || "Order Confirmed!" }}
            </h1>

            <p
                v-if="props.subtitle"
                class="text-sm leading-relaxed text-gray-600 sm:text-base subtitle"
            >
                {{ props.subtitle }}
            </p>
        </div>
    </div>
</template>

<style scoped>
/* Custom animations that work with Tailwind */
.success-card {
    transform: translateY(20px);
    opacity: 0;
    animation: slideUp 0.8s ease-out 0.2s forwards;
}

@keyframes slideUp {
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.success-icon {
    transform: scale(0);
    animation: bounceIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) 0.5s
        forwards;
}

@keyframes bounceIn {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

.checkmark-path {
    stroke-dasharray: 100;
    stroke-dashoffset: 100;
    animation: drawCheck 0.8s ease-out 1s forwards;
}

@keyframes drawCheck {
    to {
        stroke-dashoffset: 0;
    }
}

.title {
    opacity: 0;
    animation: fadeInUp 0.6s ease-out 1.2s forwards;
}

.subtitle {
    opacity: 0;
    animation: fadeInUp 0.6s ease-out 1.4s forwards;
}

.order-details {
    opacity: 0;
    animation: fadeInUp 0.6s ease-out 1.6s forwards;
}

.cta-button {
    opacity: 0;
    animation: fadeInUp 0.6s ease-out 1.8s forwards;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }

    from {
        opacity: 0;
        transform: translateY(20px);
    }
}
</style>

<template>
    <div class="three-dots-loading">
        <span></span>
        <span></span>
        <span></span>
    </div>
</template>

<style scoped>
/* Three Dots Loading */
.three-dots-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 24px;
}

.three-dots-loading span {
    display: block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: theme('colors.gray.300'); /* Replace with your primary color if needed */
    opacity: 0.7;
    animation: three-dots-bounce 1s infinite both;
    -moz-animation: three-dots-bounce 1s infinite both;
    -ms-animation: three-dots-bounce 1s infinite both;
    -o-animation: three-dots-bounce 1s infinite both;
    -webkit-animation: three-dots-bounce 1s infinite both;
}

.three-dots-loading span:nth-child(2) {
    animation-delay: 0.2s;
}
.three-dots-loading span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes three-dots-bounce {
    0%,
    80%,
    100% {
        transform: scale(0.7);
        opacity: 0.7;
    }
    40% {
        transform: scale(1.2);
        opacity: 1;
    }
}
</style>

<template>
    <div
        class="min-h-screen flex flex-col justify-center items-center py-6 px-6 bg-gradient-to-t from-[#E0BEFF80] from-80% to-white relative"
    >
        <img
            src="/storage/couple.png"
            alt="Couple"
            class="absolute bottom-0 left-0 hidden object-contain h-auto md:block md:w-1/3 max-h-[90vh]"
        />
        <img
            src="/storage/clothes_rack.png"
            alt=""
            class="absolute bottom-0 right-0 hidden object-contain object-right-bottom h-auto md:block md:w-1/3 max-h-[90vh]"
        />
        <div
            data-aos="fade-up"
            data-aos-duration="300"
            class="z-10 w-full max-w-sm px-8 py-8 bg-white shadow-md rounded-2xl"
        >
            <div class="w-full mx-auto">
                <slot name="logo" />
            </div>
            <slot />
        </div>
    </div>
</template>

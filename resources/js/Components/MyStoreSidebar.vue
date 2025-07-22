<script setup>
import { Link } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";

const props = defineProps({
    responsive: {
        type: Boolean,
        default: false,
    },
});

const menus = [
    {
        name: "Dashboard",
        href: route("my-store.dashboard"),
        active: route().current("my-store.dashboard"),
        icon: `
            <svg 
                xmlns="http://www.w3.org/2000/svg" 
                width="26" 
                height="27" 
                viewBox="0 0 26 27" 
                class="fill-primary"
            >
                <rect opacity="0.01" y="0.695312" width="26" height="26" />
                <path d="M9.75 3.94531H5.41667C4.22005 3.94531 3.25 4.91536 3.25 6.11198V10.4453C3.25 11.6419 4.22005 12.612 5.41667 12.612H9.75C10.9466 12.612 11.9167 11.6419 11.9167 10.4453V6.11198C11.9167 4.91536 10.9466 3.94531 9.75 3.94531Z"/>
                <path d="M20.5833 3.94531H16.25C15.0534 3.94531 14.0833 4.91536 14.0833 6.11198V10.4453C14.0833 11.6419 15.0534 12.612 16.25 12.612H20.5833C21.7799 12.612 22.75 11.6419 22.75 10.4453V6.11198C22.75 4.91536 21.7799 3.94531 20.5833 3.94531Z"/>
                <path d="M9.75 14.7783H5.41667C4.22005 14.7783 3.25 15.7484 3.25 16.945V21.2783C3.25 22.4749 4.22005 23.445 5.41667 23.445H9.75C10.9466 23.445 11.9167 22.4749 11.9167 21.2783V16.945C11.9167 15.7484 10.9466 14.7783 9.75 14.7783Z"/>
                <path d="M20.5833 14.7783H16.25C15.0534 14.7783 14.0833 15.7484 14.0833 16.945V21.2783C14.0833 22.4749 15.0534 23.445 16.25 23.445H20.5833C21.7799 23.445 22.75 22.4749 22.75 21.2783V16.945C22.75 15.7484 21.7799 14.7783 20.5833 14.7783Z"/>
            </svg>`,
    },
    {
        name: "Info Toko",
        href: route("my-store.store.edit"),
        active: route().current("my-store.store.edit"),
        icon: `
            <svg 
                xmlns="http://www.w3.org/2000/svg" 
                width="26" 
                height="27" 
                viewBox="0 0 26 27" 
                class="fill-primary"
            >
                <rect opacity="0.01" x="26" y="26.6953" width="26" height="26" transform="rotate(180 26 26.6953)"/>
                <path d="M3.51 8.59243L12.74 13.6083C12.9025 13.6949 13.0975 13.6949 13.26 13.6083L22.49 8.59243C22.6622 8.5074 22.7665 8.32734 22.7547 8.13567C22.7429 7.94399 22.6172 7.77814 22.4358 7.71493L13.2058 3.94493C13.074 3.89075 12.926 3.89075 12.7942 3.94493L3.56416 7.71493C3.38282 7.77814 3.25713 7.94399 3.24529 8.13567C3.23346 8.32734 3.33781 8.5074 3.51 8.59243Z"/>
                <path d="M22.4359 12.2434L20.4534 11.3984L13.26 15.3093C13.0975 15.3959 12.9025 15.3959 12.74 15.3093L5.54669 11.3984L3.56419 12.2434C3.39307 12.3333 3.28589 12.5106 3.28589 12.7039C3.28589 12.8971 3.39307 13.0744 3.56419 13.1643L12.7942 18.4726C12.9538 18.5702 13.1546 18.5702 13.3142 18.4726L22.5442 13.1643C22.7072 13.0558 22.7954 12.865 22.7725 12.6706C22.7497 12.4762 22.6195 12.3111 22.4359 12.2434Z"/>
                <path d="M22.4359 17.054L20.7459 16.3174L13.26 20.3907C13.0975 20.4774 12.9025 20.4774 12.74 20.3907L5.25419 16.3174L3.56419 17.054C3.38819 17.1453 3.27771 17.327 3.27771 17.5253C3.27771 17.7236 3.38819 17.9053 3.56419 17.9965L12.7942 23.4132C12.9567 23.4999 13.1517 23.4999 13.3142 23.4132L22.5442 17.9965C22.7121 17.8866 22.8036 17.6913 22.7807 17.4919C22.7578 17.2925 22.6243 17.1231 22.4359 17.054Z"/>
            </svg>`,
    },
    // {
    //     name: "Sertifikat",
    //     href: route("my-store.certificate"),
    //     active: route().current("my-store.certificate"),
    //     icon: `
    //         <svg
    //             xmlns="http://www.w3.org/2000/svg"
    //             width="26"
    //             height="27"
    //             viewBox="0 0 26 27"
    //             class="fill-primary"
    //         >
    //             <rect opacity="0.01" y="0.695312" width="26" height="26"/>
    //             <path d="M15.7737 2.8625C16.0793 2.86325 16.3707 2.9931 16.5755 3.21992L21.3851 8.63691C21.5656 8.8355 21.6665 9.09414 21.6673 9.3625V21.8215C21.6587 22.5483 21.3612 23.2423 20.8411 23.7502C20.3211 24.2579 19.6206 24.5381 18.8939 24.5295H7.10675C6.38003 24.5381 5.67952 24.2579 5.15948 23.7502C4.63944 23.2423 4.34196 22.5483 4.33331 21.8215V5.57148C4.34188 4.84456 4.63938 4.15069 5.15948 3.64277C5.67954 3.1349 6.3799 2.85389 7.10675 2.8625H15.7737ZM9.75031 18.0295C9.15205 18.0295 8.66739 18.5143 8.6673 19.1125C8.6673 19.7108 9.152 20.1955 9.75031 20.1955H16.2503C16.8486 20.1955 17.3333 19.7108 17.3333 19.1125C17.3332 18.5143 16.8486 18.0295 16.2503 18.0295H9.75031ZM9.75031 13.6965C9.152 13.6965 8.6673 14.1812 8.6673 14.7795C8.66739 15.3777 9.15205 15.8625 9.75031 15.8625H13.0003C13.5986 15.8625 14.0832 15.3777 14.0833 14.7795C14.0833 14.1812 13.5986 13.6965 13.0003 13.6965H9.75031ZM15.1663 8.4416C15.1286 8.90696 15.4712 9.31674 15.9359 9.3625H19.2181L15.1663 5.02949V8.4416Z"/>
    //         </svg>`,
    // },
    // {
    //     name: "Produk",
    //     href: route("my-store.product"),
    //     active: route().current("my-store.product"),
    //     icon: `
    //         <svg
    //             xmlns="http://www.w3.org/2000/svg"
    //             width="26"
    //             height="27"
    //             viewBox="0 0 26 27"
    //             class="fill-primary"
    //         >
    //             <rect opacity="0.01" y="0.695312" width="26" height="26"/>
    //             <path d="M12.1875 13.5111L3.25 9.75195V18.1261C3.25962 18.8391 3.68236 19.4817 4.33333 19.7728L12.1225 23.4453H12.1875V13.5111Z"/>
    //             <path d="M13 12.0701L22.2192 8.20256C22.064 8.03018 21.8762 7.89026 21.6667 7.79089L13.8667 4.14006C13.3181 3.8804 12.6819 3.8804 12.1333 4.14006L4.33332 7.79089C4.12376 7.89026 3.93598 8.03018 3.78082 8.20256L13 12.0701Z"/>
    //             <path d="M13.8125 13.5111V23.4453H13.8667L21.6667 19.7728C22.3141 19.4834 22.7362 18.846 22.75 18.137V9.75195L13.8125 13.5111Z"/>
    //         </svg>`,
    // },
];
</script>

<template>
    <aside
        class="fixed top-0 left-0 w-0 h-screen overflow-y-hidden transition-all duration-300 ease-in-out md:w-64"
        :class="{
            '!static w-full': props.responsive,
        }"
    >
        <nav
            class="h-full bg-primary"
            :class="{
                '!py-4': props.responsive,
            }"
        >
            <!-- Logo -->
            <div class="flex items-center px-6 py-2.5 mb-1 shrink-0">
                <Link :href="route('home')">
                    <img
                        :src="`/storage/${$page.props.setting.logo}`"
                        alt="Logo"
                        class="w-auto h-8 sm:h-12"
                    />
                </Link>
            </div>

            <ul>
                <li v-for="menu in menus" :key="menu.name">
                    <NavLink
                        :href="menu.href"
                        :active="menu.active"
                        active-class="!bg-secondary !border-[#CE4DB1] text-primary"
                        class="px-6 py-4 w-full bg-transparent hover:bg-secondary/10 hover:border-l-4 hover:border-[#CE4DB1] border-l-4 transition-all duration-300 ease-in-out border-primary"
                        :class="{
                            '!px-4 !py-2.5': props.responsive,
                            '!text-primary': menu.active,
                        }"
                    >
                        <span
                            v-if="menu.icon"
                            v-html="menu.icon"
                            class="me-2"
                            :class="{
                                '[&>svg]:fill-white/80': !menu.active,
                                '[&>svg]:size-6 me-1.5': props.responsive,
                            }"
                        ></span>
                        <p
                            class="font-bold line-clamp-1 overflow-ellipsis"
                            :class="{
                                'font-semibold text-sm': props.responsive,
                            }"
                        >
                            {{ menu.name }}
                        </p>
                    </NavLink>
                </li>
            </ul>
            <slot name="extra"></slot>
        </nav>
    </aside>
</template>

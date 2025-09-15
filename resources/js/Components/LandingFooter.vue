<script setup>
import { Link } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import IconTikTok from "@/Icons/IconTikTok.vue";
import IconFacebook from "@/Icons/IconFacebook.vue";
import IconInstagram from "@/Icons/IconInstagram.vue";
import { getImageUrl } from "@/plugins/helpers";

const page = usePage();
const store = page.props.store || {};
const setting = {
    ...page.props.setting,
    social_links: JSON.parse(page.props.setting.social_links),
};

const isHome = route().current("home");
</script>

<template>
    <footer
        class="bg-primary-box px-6 pt-12 sm:pt-20 pb-6 sm:px-12 lg:px-[100px]"
    >
        <div class="mx-auto max-w-7xl">
            <div
                class="flex flex-col items-start justify-between gap-8 mb-12 sm:flex-row"
            >
                <!-- Identity -->
                <div
                    class="flex flex-col items-start w-full text-start sm:2/3 md:w-1/3"
                >
                    <Link :href="route('home')" class="hidden sm:block">
                        <img
                            :src="getImageUrl($page.props.setting.logo_white)"
                            alt="Logo"
                            class="h-20 mb-8"
                        />
                    </Link>

                    <Link :href="route('home')">
                        <h2
                            class="mb-4 text-2xl font-semibold text-white sm:hidden"
                        >
                            {{ setting.site_name }}
                        </h2>
                    </Link>

                    <div class="text-start">
                        <p v-if="setting.address" class="mb-2 text-gray-100">
                            {{ setting.address }}
                        </p>
                        <p
                            v-if="setting.contact_email"
                            class="mb-2 text-gray-100"
                        >
                            <a
                                :href="`mailto:${setting.contact_email}`"
                                target="_blank"
                                class="text-gray-100 hover:text-white"
                            >
                                {{ setting.contact_email }}
                            </a>
                        </p>
                        <p
                            v-if="setting.contact_phone"
                            class="mb-2 text-gray-100"
                        >
                            <a
                                :href="`https://wa.me/${setting.contact_phone}`"
                                target="_blank"
                                class="text-gray-100 hover:text-white"
                            >
                                {{ setting.contact_phone }}
                            </a>
                        </p>
                        <div
                            class="flex items-center justify-start mt-4 space-x-4"
                        >
                            <template
                                v-for="(link, key) in setting.social_links"
                                :key="key"
                            >
                                <a :href="link" target="_blank">
                                    <IconInstagram
                                        v-if="key == 'instagram'"
                                        class="!fill-white/80 hover:!fill-white max-sm:!size-6"
                                    />
                                    <IconFacebook
                                        v-else-if="key == 'facebook'"
                                        class="!fill-white/80 hover:!fill-white max-sm:!size-7"
                                    />
                                    <IconTikTok
                                        v-else-if="key == 'tiktok'"
                                        class="!fill-white/80 hover:!fill-white max-sm:!size-7"
                                    />
                                </a>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Quick Access -->
                <div
                    class="flex flex-wrap justify-start w-full grid-cols-1 gap-y-8 gap-x-12 sm:grid md:grid-cols-3 sm:1/3 md:w-2/3"
                >
                    <div class="text-start sm:text-start">
                        <h3 class="mb-4 text-lg font-semibold text-white/90">
                            Identitas Toko
                        </h3>
                        <ul class="space-y-2">
                            <li>
                                <NavLink
                                    :href="
                                        isHome
                                            ? '#advantages'
                                            : route('home') + '#advantages'
                                    "
                                    class="text-gray-100 hover:text-white"
                                >
                                    Keunggulan
                                </NavLink>
                            </li>
                            <li>
                                <NavLink
                                    :href="
                                        isHome
                                            ? '#certificates'
                                            : route('home') + '#certificates'
                                    "
                                    class="text-gray-100 hover:text-white"
                                >
                                    Sertifikasi
                                </NavLink>
                            </li>
                        </ul>
                    </div>
                    <div class="text-start">
                        <NavLink :href="route('catalog')">
                            <h3
                                class="mb-4 text-lg font-semibold text-white/90 hover:text-white"
                            >
                                Katalog Produk
                            </h3>
                        </NavLink>
                        <ul class="space-y-2">
                            <li>
                                <NavLink
                                    :href="
                                        route('catalog', {
                                            categories: 'Gamis',
                                        })
                                    "
                                    class="text-gray-100 hover:text-white"
                                >
                                    Gamis
                                </NavLink>
                            </li>
                            <li>
                                <NavLink
                                    :href="
                                        route('catalog', {
                                            categories: 'Aksesoris Muslim',
                                        })
                                    "
                                    class="text-gray-100 hover:text-white"
                                >
                                    Aksesoris
                                </NavLink>
                            </li>
                            <li>
                                <NavLink
                                    :href="
                                        route('catalog', {
                                            categories: 'Baju Anak',
                                        })
                                    "
                                    class="text-gray-100 hover:text-white"
                                >
                                    Baju Anak
                                </NavLink>
                            </li>
                        </ul>
                    </div>
                    <div class="text-start">
                        <h3 class="mb-4 text-lg font-semibold text-white/90">
                            Gabung
                        </h3>
                        <ul class="space-y-2">
                            <li>
                                <NavLink
                                    href="#about"
                                    class="text-gray-100 hover:text-white"
                                >
                                    Agen
                                </NavLink>
                            </li>
                            <li>
                                <NavLink
                                    :href="route('home')"
                                    class="text-gray-100 hover:text-white"
                                >
                                    Reseller
                                </NavLink>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-100 sm:text-base">
                    &copy; {{ new Date().getFullYear() }}
                    {{ setting.site_name }}. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</template>

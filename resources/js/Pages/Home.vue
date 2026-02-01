<script setup lang="ts">
import LandingLayout from "@/Layouts/LandingLayout.vue";
import ProductCard from "@/Components/ProductCard.vue";
import LandingSection from "@/Components/LandingSection.vue";
import JoinUs from "@/Components/JoinUs.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Link, usePage } from "@inertiajs/vue3";
import CategoryCard from "@/Components/CategoryCard.vue";
import { getImageUrl } from "@/plugins/helpers";
import { onMounted } from "vue";
import CustomPageProps from "@/types/model/CustomPageProps";
import { useDialogStore } from "@/stores/dialog-store";
import CarouselBanner from "@/Components/CarouselBanner.vue";

const props = defineProps({
    store: Object,
    brands: {
        type: Array as () => BrandEntity[],
    },
    categories: {
        type: Array as () => CategoryEntity[],
        default: () => [],
    },
    popularProducts: {
        type: Array as () => ProductEntity[],
        default: () => [],
    },
    promotions: {
        type: Array as () => PromotionEntity[],
        default: () => [],
    },
});

const page = usePage<CustomPageProps>();

onMounted(() => {
    if (page.props.flash?.error) {
        useDialogStore().openErrorDialog(page.props.flash.error);
    }
});
</script>

<template>
    <LandingLayout title="Beranda">
        <!-- Banner -->
        <CarouselBanner
            v-if="props.promotions.length"
            :items="
                props.promotions.map((promotion) => ({
                    image: promotion.image,
                    alt: promotion.name,
                    link: promotion.redirection_url || null,
                }))
            "
            class="w-full"
        />

        <img
            v-else
            src="/storage/promotion_banner.png"
            alt="Banner Promosi"
            class="object-cover w-full"
        />

        <!-- Brands -->
        <!-- <div
            v-if="
                props.brands.map((brand) => brand.logo).filter((logo) => logo)
                    .length > 0
            "
            class="flex flex-row items-center justify-center gap-6 px-6 py-4 bg-secondary-box/30 max-sm:flex-wrap sm:gap-8 md:gap-x-24 sm:py-6 lg:px-40"
        >
            <template v-for="brand in props.brands || []" :key="brand.id">
                <img
                    v-if="brand.logo"
                    :src="getImageUrl(brand.logo)"
                    :alt="brand.name"
                    data-aos="zoom-in"
                    data-aos-duration="600"
                    class="object-contain h-8 max-w-12 sm:max-w-20 lg:max-w-32 sm:h-16 lg:h-20"
                />
            </template>
        </div> -->

        <div class="p-6 sm:p-12 md:p-[100px] flex flex-col gap-12 lg:gap-20">
            <!-- Best Seller Products -->
            <LandingSection>
                <div
                    class="flex flex-col items-start justify-center w-full gap-4 mx-auto max-w-7xl"
                >
                    <div
                        class="flex flex-col items-start justify-center w-full gap-4"
                    >
                        <div
                            data-aos="fade-up"
                            data-aos-duration="600"
                            class="flex items-center justify-between w-full gap-12 mb-4"
                        >
                            <h1
                                class="text-xl font-bold sm:text-2xl text-nowrap"
                            >
                                Produk Terlaris
                            </h1>
                            <TextInput
                                placeholder="Cari produk..."
                                bgClass="bg-gray-100 focus:bg-white"
                                textClass="text-sm !ps-4"
                                class="relative w-full max-w-md"
                                @keyup.enter="
                                    (e) => {
                                        $inertia.get(route('catalog'), {
                                            search: e.target.value,
                                        });
                                    }
                                "
                            >
                                <template #suffix>
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        class="absolute -translate-y-1/2 fill-gray-400 right-3 top-1/2 size-5"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M11 17C11.7879 17 12.5681 16.8448 13.2961 16.5433C14.0241 16.2417 14.6855 15.7998 15.2426 15.2426C15.7998 14.6855 16.2417 14.0241 16.5433 13.2961C16.8448 12.5681 17 11.7879 17 11C17 10.2121 16.8448 9.43185 16.5433 8.7039C16.2417 7.97595 15.7998 7.31451 15.2426 6.75736C14.6855 6.20021 14.0241 5.75825 13.2961 5.45672C12.5681 5.15519 11.7879 5 11 5C9.4087 5 7.88258 5.63214 6.75736 6.75736C5.63214 7.88258 5 9.4087 5 11C5 12.5913 5.63214 14.1174 6.75736 15.2426C7.88258 16.3679 9.4087 17 11 17ZM11 19C13.1217 19 15.1566 18.1571 16.6569 16.6569C18.1571 15.1566 19 13.1217 19 11C19 8.87827 18.1571 6.84344 16.6569 5.34315C15.1566 3.84285 13.1217 3 11 3C8.87827 3 6.84344 3.84285 5.34315 5.34315C3.84285 6.84344 3 8.87827 3 11C3 13.1217 3.84285 15.1566 5.34315 16.6569C6.84344 18.1571 8.87827 19 11 19Z"
                                        />
                                        <path
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M15.3201 15.2903C15.5082 15.1035 15.7629 14.9991 16.0281 15C16.2933 15.0009 16.5472 15.1072 16.7341 15.2953L20.7091 19.2953C20.8908 19.4844 20.9909 19.7373 20.9879 19.9995C20.9849 20.2618 20.879 20.5123 20.6931 20.6972C20.5071 20.8822 20.256 20.9866 19.9937 20.9881C19.7315 20.9896 19.4791 20.8881 19.2911 20.7053L15.3161 16.7053C15.1291 16.5172 15.0245 16.2626 15.0253 15.9975C15.026 15.7323 15.1321 15.4783 15.3201 15.2913V15.2903Z"
                                        />
                                    </svg>
                                </template>
                            </TextInput>
                        </div>
                        <div
                            class="grid w-full grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4 sm:gap-6"
                        >
                            <div
                                v-for="(
                                    product, index
                                ) in props.popularProducts || []"
                                :key="product.id"
                                data-aos="fade-up"
                                data-aos-duration="600"
                            >
                                <ProductCard
                                    :name="product.name"
                                    :basePrice="
                                        product.lowest_base_selling_price
                                    "
                                    :discount="product.discount"
                                    :finalPrice="
                                        product.lowest_final_selling_price
                                    "
                                    :image="
                                        (product.preview_url as string) || null
                                    "
                                    :description="product.brand?.name"
                                    :slug="product.slug"
                                />
                            </div>
                        </div>
                    </div>

                    <Link :href="route('catalog')" class="mx-auto mt-10">
                        <PrimaryButton> Lihat Semua Produk </PrimaryButton>
                    </Link>
                </div>
            </LandingSection>

            <!-- Categories -->
            <LandingSection
                v-if="props.categories && props.categories.length > 0"
                class="min-h-[30vh]"
            >
                <div
                    class="flex flex-col items-start justify-center w-full gap-4 mx-auto max-w-7xl"
                >
                    <div
                        data-aos="fade-up"
                        data-aos-duration="600"
                        class="flex items-center justify-between w-full gap-12 mb-4"
                    >
                        <h1 class="text-xl font-bold sm:text-2xl text-nowrap">
                            Kategori Populer
                        </h1>
                        <!-- Selengkapnya -->
                        <Link
                            :href="route('catalog')"
                            class="text-sm text-primary hover:underline"
                        >
                            Selengkapnya
                        </Link>
                    </div>
                    <div
                        class="grid w-full grid-cols-3 gap-6 md:grid-cols-4 lg:grid-cols-5 sm:gap-9"
                    >
                        <div
                            v-for="(category, index) in props.categories || []"
                            :key="category.id"
                            data-aos="fade-up"
                            data-aos-duration="600"
                        >
                            <Link
                                :href="
                                    route('catalog', {
                                        categories: category.name,
                                    })
                                "
                            >
                                <CategoryCard
                                    :name="category.name"
                                    :image="getImageUrl(category.image)"
                                />
                            </Link>
                        </div>
                    </div>
                </div>
            </LandingSection>

            <!-- Join Us -->
            <LandingSection v-if="!$page.props.auth.user" id="join">
                <JoinUs />
            </LandingSection>
        </div>
    </LandingLayout>
</template>

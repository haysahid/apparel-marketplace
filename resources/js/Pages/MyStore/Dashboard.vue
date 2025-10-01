<script setup>
import SummaryCard from "@/Components/SummaryCard.vue";
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import MyStoreLayout from "@/Layouts/MyStoreLayout.vue";
import DefaultCard from "@/Components/DefaultCard.vue";

const props = defineProps({
    productCount: {
        type: Number,
        default: 0,
    },
    userCount: {
        type: Number,
        default: 0,
    },
});

const page = usePage();

const chartData = {
    options: {
        chart: {
            id: "vuechart-example",
        },
        xaxis: {
            categories: [
                1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18,
                19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30,
            ],
        },
        colors: ["#C28C42"],
        stroke: {
            width: 3,
        },
        markers: {
            size: 6,
        },
    },
    series: [
        {
            name: "series-1",
            data: Array.from({ length: 30 }, (_, i) =>
                Math.round(50 + i * 2 + Math.random() * 10)
            ),
        },
    ],
};

onMounted(() => {
    if (page.props.selected_store_id) {
        localStorage.setItem("selected_store_id", page.props.selected_store_id);
    }
});
</script>

<template>
    <MyStoreLayout title="Dashboard" :showTitle="true">
        <div class="">
            <div class="flex flex-col gap-4">
                <div
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <SummaryCard
                        title="Total Produk"
                        :value="props.productCount"
                    >
                        <template #icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="60"
                                height="61"
                                viewBox="0 0 60 61"
                                class="size-10"
                            >
                                <path
                                    opacity="0.21"
                                    d="M37 0.695312C49.7025 0.695312 60 10.9928 60 23.6953V37.6953C60 50.3979 49.7025 60.6953 37 60.6953H23C10.2975 60.6953 0 50.3979 0 37.6953V23.6953C0 10.9928 10.2975 0.695312 23 0.695312H37Z"
                                    fill="#FEC53D"
                                />
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M15 25.0115L27.9005 32.4596C28.0394 32.5398 28.1851 32.5977 28.3333 32.6345V47.0797L15.9201 39.7335C15.3498 39.396 15 38.7826 15 38.1199V25.0115ZM45 24.8135V38.1199C45 38.7826 44.6502 39.396 44.0799 39.7335L31.6667 47.0797V32.5079C31.6969 32.4928 31.7269 32.4767 31.7566 32.4596L45 24.8135Z"
                                    fill="#FEC53D"
                                />
                                <path
                                    opacity="0.499209"
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M15.4053 21.3967C15.5628 21.1978 15.7617 21.0296 15.9936 20.9061L29.1186 13.9154C29.6696 13.6219 30.3305 13.6219 30.8815 13.9154L44.0065 20.9061C44.1852 21.0013 44.3444 21.123 44.4801 21.265L30.0899 29.5731C29.9953 29.6278 29.9081 29.6903 29.8286 29.7593C29.7491 29.6903 29.6618 29.6278 29.5672 29.5731L15.4053 21.3967Z"
                                    fill="#FEC53D"
                                />
                            </svg>
                        </template>
                    </SummaryCard>
                    <SummaryCard
                        title="Total Pengguna"
                        :value="props.userCount"
                    >
                        <template #icon>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="60"
                                height="61"
                                viewBox="0 0 60 61"
                                class="size-10"
                            >
                                <path
                                    opacity="0.21"
                                    d="M37 0.695312C49.7025 0.695312 60 10.9928 60 23.6953V37.6953C60 50.3979 49.7025 60.6953 37 60.6953H23C10.2975 60.6953 0 50.3979 0 37.6953V23.6953C0 10.9928 10.2975 0.695312 23 0.695312H37Z"
                                    fill="#8280FF"
                                />
                                <path
                                    opacity="0.587821"
                                    d="M37.9996 25.3623C40.2088 25.3623 41.9996 27.1532 41.9996 29.3623C41.9995 31.5713 40.2087 33.3623 37.9996 33.3623C35.7908 33.3621 33.9998 31.5712 33.9996 29.3623C33.9996 27.1533 35.7906 25.3625 37.9996 25.3623ZM25.9996 18.6953C28.945 18.6953 31.3334 21.083 31.3336 24.0283C31.3336 26.9738 28.9452 29.3623 25.9996 29.3623C23.0543 29.3621 20.6666 26.9737 20.6666 24.0283C20.6668 21.0831 23.0544 18.6955 25.9996 18.6953Z"
                                    fill="#8280FF"
                                />
                                <path
                                    d="M25.9774 32.0283C32.3611 32.0283 37.6061 35.0858 37.997 41.6279C38.0125 41.8886 37.9966 42.6953 36.995 42.6953H14.9696C14.6351 42.6949 13.9727 41.9735 14.0009 41.627C14.5178 35.2637 19.6824 32.0284 25.9774 32.0283ZM37.4686 34.6973C42.0103 34.7471 45.7187 37.0419 45.9979 41.8945C46.0092 42.09 45.9977 42.6953 45.2743 42.6953H40.1337C40.1337 39.6946 39.1416 36.9254 37.4686 34.6973Z"
                                    fill="#8280FF"
                                />
                            </svg>
                        </template>
                    </SummaryCard>
                </div>

                <DefaultCard>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold sm:text-xl">
                            Traffic Pengunjung
                        </h3>
                        <div
                            class="flex items-center gap-2 py-2 pl-4 pr-2 bg-white border rounded-lg border-primary/80"
                        >
                            <p class="font-medium text-primary">Bulan</p>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                class="size-4 fill-primary"
                            >
                                <path
                                    d="M18.6054 7.3997C18.4811 7.273 18.3335 7.17248 18.1709 7.10389C18.0084 7.0353 17.8342 7 17.6583 7C17.4823 7 17.3081 7.0353 17.1456 7.10389C16.9831 7.17248 16.8355 7.273 16.7112 7.3997L11.4988 12.7028L6.28648 7.3997C6.03529 7.14415 5.69462 7.00058 5.33939 7.00058C4.98416 7.00058 4.64348 7.14415 4.3923 7.3997C4.14111 7.65526 4 8.00186 4 8.36327C4 8.72468 4.14111 9.07129 4.3923 9.32684L10.5585 15.6003C10.6827 15.727 10.8304 15.8275 10.9929 15.8961C11.1554 15.9647 11.3296 16 11.5055 16C11.6815 16 11.8557 15.9647 12.0182 15.8961C12.1807 15.8275 12.3284 15.727 12.4526 15.6003L18.6188 9.32684C19.1293 8.80747 19.1293 7.93274 18.6054 7.3997Z"
                                />
                            </svg>
                        </div>
                    </div>

                    <apexchart
                        type="line"
                        height="360"
                        :options="chartData.options"
                        :series="chartData.series"
                    ></apexchart>
                </DefaultCard>
            </div>
        </div>
    </MyStoreLayout>
</template>

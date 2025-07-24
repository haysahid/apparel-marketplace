<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import ProductForm from "./ProductForm.vue";
</script>

<template>
    <AdminLayout title="Tambah Produk" :showTitle="true">
        <template #icon>
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="26"
                height="27"
                viewBox="0 0 26 27"
                class="fill-primary"
            >
                <rect opacity="0.01" y="0.695312" width="26" height="26" />
                <path
                    d="M12.1875 13.5111L3.25 9.75195V18.1261C3.25962 18.8391 3.68236 19.4817 4.33333 19.7728L12.1225 23.4453H12.1875V13.5111Z"
                />
                <path
                    d="M13 12.0701L22.2192 8.20256C22.064 8.03018 21.8762 7.89026 21.6667 7.79089L13.8667 4.14006C13.3181 3.8804 12.6819 3.8804 12.1333 4.14006L4.33332 7.79089C4.12376 7.89026 3.93598 8.03018 3.78082 8.20256L13 12.0701Z"
                />
                <path
                    d="M13.8125 13.5111V23.4453H13.8667L21.6667 19.7728C22.3141 19.4834 22.7362 18.846 22.75 18.137V9.75195L13.8125 13.5111Z"
                />
            </svg>
        </template>

        <div class="md:px-11 max-w-7xl">
            <ProductForm />
        </div>
    </AdminLayout>
</template>

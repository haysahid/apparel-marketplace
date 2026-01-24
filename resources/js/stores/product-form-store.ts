import cookieManager from "@/plugins/cookie-manager";
import CustomPageProps from "@/types/model/CustomPageProps";
import { router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { defineStore } from "pinia";
import { computed, ref, watch } from "vue";
import { useDialogStore } from "./dialog-store";

export const useProductFormStore = defineStore("product_form", () => {
    const token = `Bearer ${cookieManager.getItem("access_token")}`;
    const selectedStoreId = cookieManager.getItem("selected_store_id");

    const selectedProductKey = "selected_product";
    const productFormKey = "product_form";
    const newProductFormKey = "new_product_form";

    const dialogStore = useDialogStore();

    const product = ref<ProductEntity | null>(
        localStorage.getItem(selectedProductKey)
            ? JSON.parse(localStorage.getItem(selectedProductKey) as string)
            : null,
    );

    const form = ref<
        ProductEntity & { errors: { [key: string]: string | null } }
    >(
        localStorage.getItem(productFormKey)
            ? {
                  ...JSON.parse(localStorage.getItem(productFormKey) as string),
                  errors: {},
              }
            : {
                  name: product.value?.name || null,
                  sku_prefix: product.value?.sku_prefix || null,
                  brand_id: product.value?.brand_id || null,
                  brand: product.value?.brand || null,
                  discount: product.value?.discount || 0,
                  description: product.value?.description || null,
                  categories: product.value?.categories || [],
                  images: product.value?.images || [],
                  links: product.value?.links || [],
                  variants: product.value?.variants || [],
                  errors: {},
              },
    );

    const newProductForm = localStorage.getItem(newProductFormKey);
    const clearNewProductForm = () => {
        localStorage.removeItem(newProductFormKey);
    };

    watch(
        () => product.value,
        (newProduct) => {
            localStorage.setItem(
                selectedProductKey,
                JSON.stringify(newProduct),
            );
        },
        { deep: true },
    );

    watch(
        () => form.value,
        (newForm) => {
            localStorage.setItem(productFormKey, JSON.stringify(newForm));

            if (!product.value) {
                localStorage.setItem(
                    newProductFormKey,
                    JSON.stringify(newForm),
                );
            }
        },
        { deep: true },
    );

    const initializeForm = (selectedProduct: ProductEntity | null = null) => {
        if (selectedProduct) {
            form.value = {
                name: selectedProduct?.name || null,
                sku_prefix: selectedProduct?.sku_prefix || null,
                brand_id: selectedProduct?.brand_id || null,
                brand: selectedProduct?.brand || null,
                discount: selectedProduct?.discount || 0,
                description: selectedProduct?.description || null,
                categories: selectedProduct?.categories || [],
                images: selectedProduct?.images || [],
                links: selectedProduct?.links || [],
                variants: selectedProduct?.variants || [],
                errors: {},
            } as ProductEntity & {
                errors: { [key: string]: string | null };
            };
        } else if (product.value) {
            if (newProductForm) {
                form.value = {
                    ...JSON.parse(newProductForm),
                    errors: {},
                };
            } else {
                form.value = {
                    name: null,
                    sku_prefix: null,
                    brand_id: null,
                    brand: null,
                    discount: 0,
                    description: null,
                    categories: [],
                    images: [],
                    links: [],
                    variants: [],
                    errors: {},
                } as ProductEntity & {
                    errors: { [key: string]: string | null };
                };
            }
        }

        product.value = selectedProduct;

        if (!selectedProduct) {
            localStorage.removeItem(selectedProductKey);
        }
    };

    const page = usePage<CustomPageProps>();

    const brands = ref<BrandEntity[]>(page.props.brands || []);
    const brandSearch = ref("");
    const filteredBrands = computed(() => {
        return brands.value.filter((brand) =>
            brand.name.toLowerCase().includes(brandSearch.value.toLowerCase()),
        );
    });

    const categories = ref(page.props.categories || []);
    const categorySearch = ref("");
    const filteredCategories = computed(() => {
        return categories.value.filter((category) =>
            category.name
                .toLowerCase()
                .includes(categorySearch.value.toLowerCase()),
        );
    });

    // Variants
    function deleteVariant(variant: ProductVariantEntity) {
        const token = `Bearer ${cookieManager.getItem("access_token")}`;

        axios
            .delete(
                `${page.props.ziggy.url}/api/my-store/product-variant/${variant.id}`,
                {
                    headers: {
                        Authorization: token,
                        "X-Selected-Store-ID": selectedStoreId,
                    },
                },
            )
            .then((response) => {
                dialogStore.openSuccessDialog(response.data.meta.message);
                getVariants();
            })
            .catch((error) => {
                if (error.response?.data?.error) {
                    dialogStore.openErrorDialog(error.response.data.error);
                }
            });
    }

    function getVariants() {
        axios
            .get(
                `${page.props.ziggy.url}/api/my-store/product/${product.value.id}`,
                {
                    headers: {
                        Authorization: token,
                        "X-Selected-Store-ID": selectedStoreId,
                    },
                },
            )
            .then((response) => {
                const product = response.data.result;
                form.value.variants = product.variants;
            })
            .catch((error) => {
                if (error.response?.data?.error) {
                    dialogStore.openErrorDialog(error.response.data.error);
                }
            });
    }

    const submitProduct = async ({
        autoShowDialog = true,
        onSuccess = (response: any) => {},
        onError = (error: any) => {},
        onChangeStatus = (status: string) => {},
    } = {}) => {
        if (product.value?.id) {
            onChangeStatus("loading");

            const formData = new FormData();
            formData.append("_method", "PUT");

            Object.keys(form.value).forEach((key) => {
                if (key === "images") {
                    form.value.images.forEach((image, index) => {
                        if (image.is_temporary) {
                            formData.append(
                                `temporary_images[${index}]`,
                                image.id.toString(),
                            );
                        } else {
                            formData.append(
                                `images[${index}]`,
                                image.id.toString(),
                            );
                        }
                    });
                } else if (key === "categories") {
                    form.value.categories.forEach((category, index) => {
                        formData.append(
                            `categories[${index}]`,
                            category.id.toString(),
                        );
                    });
                } else if (key === "links") {
                    form.value.links.forEach((link, index) => {
                        formData.append(
                            `links[${index}][platform_id]`,
                            link.platform_id.toString(),
                        );
                        formData.append(`links[${index}][url]`, link.url);
                    });
                } else if (key === "variants") {
                    form.value.variants.forEach((variant, index) => {
                        formData.append(
                            `variants[${index}][id]`,
                            variant.id.toString(),
                        );
                    });
                } else if (
                    form.value[key] !== null &&
                    form.value[key] !== undefined
                ) {
                    formData.append(key, form.value[key]);
                }
            });

            // router.post(
            //     route("my-store.product.update", product.value.id),
            //     formData,
            //     {
            //         onSuccess: (response) => {
            //             onChangeStatus("success");
            //             onSuccess(response);

            //             if (autoShowDialog) {
            //                 const page = usePage<CustomPageProps>();
            //                 dialogStore.openSuccessDialog(
            //                     page.props.flash.success,
            //                 );
            //             }
            //         },
            //         onError: (error) => {
            //             onChangeStatus("error");
            //             onError(error);
            //             form.value.errors = error;
            //         },
            //     },
            // );

            await axios
                .post(`/api/my-store/product/${product.value.id}`, formData, {
                    headers: {
                        Authorization: `Bearer ${cookieManager.getItem(
                            "access_token",
                        )}`,
                        "X-Selected-Store-ID":
                            cookieManager.getItem("selected_store_id"),
                    },
                })
                .then((response) => {
                    onChangeStatus("success");
                    onSuccess(response);

                    if (autoShowDialog) {
                        const page = usePage<CustomPageProps>();
                        dialogStore.openSuccessDialog(page.props.flash.success);
                    }
                })
                .catch((error) => {
                    onChangeStatus("error");
                    onError(error);
                    if (error.response?.data?.errors) {
                        form.value.errors = error.response.data.errors;
                    }

                    if (autoShowDialog && error.response?.data?.error) {
                        dialogStore.openErrorDialog(error.response.data.error);
                    }
                });
        } else {
            onChangeStatus("loading");

            const formData = new FormData();

            Object.keys(form.value).forEach((key) => {
                if (key === "images") {
                    form.value.images.forEach((image, index) => {
                        if (image.is_temporary) {
                            formData.append(
                                `temporary_images[${index}]`,
                                image.id.toString(),
                            );
                        } else {
                            formData.append(
                                `images[${index}]`,
                                image.id.toString(),
                            );
                        }
                    });
                } else if (key === "categories") {
                    form.value.categories.forEach((category, index) => {
                        formData.append(
                            `categories[${index}]`,
                            category.id.toString(),
                        );
                    });
                } else if (key === "links") {
                    form.value.links.forEach((link, index) => {
                        formData.append(
                            `links[${index}][platform_id]`,
                            link.platform_id.toString(),
                        );
                        formData.append(`links[${index}][url]`, link.url);
                    });
                } else if (key === "variants") {
                    // Skip variants on product creation
                } else if (
                    form.value[key] !== null &&
                    form.value[key] !== undefined
                ) {
                    formData.append(key, form.value[key]);
                }
            });

            // router.post(route("my-store.product.store"), formData, {
            //     onSuccess: (response) => {
            //         console.log(response);
            //         onChangeStatus("success");
            //         onSuccess(response);

            //         if (autoShowDialog) {
            //             const page = usePage<CustomPageProps>();
            //             dialogStore.openSuccessDialog(page.props.flash.success);
            //         }
            //     },
            //     onError: (error) => {
            //         onChangeStatus("error");
            //         onError(error);
            //         form.value.errors = error;

            //         if (autoShowDialog && error.error) {
            //             dialogStore.openErrorDialog(error.error);
            //         }
            //     },
            // });

            await axios
                .post("/api/my-store/product", formData, {
                    headers: {
                        Authorization: `Bearer ${cookieManager.getItem(
                            "access_token",
                        )}`,
                        "X-Selected-Store-ID":
                            cookieManager.getItem("selected_store_id"),
                    },
                })
                .then((response) => {
                    onChangeStatus("success");
                    onSuccess(response);

                    if (autoShowDialog) {
                        const page = usePage<CustomPageProps>();
                        dialogStore.openSuccessDialog(page.props.flash.success);
                    }
                })
                .catch((error) => {
                    onChangeStatus("error");
                    onError(error);
                    if (error.response?.data?.errors) {
                        form.value.errors = error.response.data.errors;
                    }

                    if (autoShowDialog && error.response?.data?.error) {
                        dialogStore.openErrorDialog(error.response.data.error);
                    }
                });
        }
    };

    const variantsToDelete = ref([]);

    const showAddLinkForm = ref(false);
    const openAddLinkForm = () => {
        showAddLinkForm.value = true;
    };

    const showAddBrandForm = ref(false);
    const showAddCategoryForm = ref(false);

    const tabs = computed(() => {
        let visibleTabs = [
            {
                title: "Informasi Produk",
                subtitle: null,
                icon: `
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="26"
                    height="27"
                    viewBox="0 0 26 27"
                    class="fill-primary"
                >
                    <rect opacity="0.01" y="0.695312" width="26" height="26"/>
                    <path d="M12.1875 13.5111L3.25 9.75195V18.1261C3.25962 18.8391 3.68236 19.4817 4.33333 19.7728L12.1225 23.4453H12.1875V13.5111Z"/>
                    <path d="M13 12.0701L22.2192 8.20256C22.064 8.03018 21.8762 7.89026 21.6667 7.79089L13.8667 4.14006C13.3181 3.8804 12.6819 3.8804 12.1333 4.14006L4.33332 7.79089C4.12376 7.89026 3.93598 8.03018 3.78082 8.20256L13 12.0701Z"/>
                    <path d="M13.8125 13.5111V23.4453H13.8667L21.6667 19.7728C22.3141 19.4834 22.7362 18.846 22.75 18.137V9.75195L13.8125 13.5111Z"/>
                </svg>
                `,
            },
        ];

        if (product.value) {
            visibleTabs.push({
                title: `Variasi Produk (${form.value.variants.length})`,
                subtitle: null,
                icon: `
                <svg 
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    class="fill-primary"
                >
                    <path d="M20.9549 8.80399V11.014C20.9549 11.2129 20.8759 11.4037 20.7353 11.5443C20.5946 11.685 20.4039 11.764 20.2049 11.764C20.006 11.764 19.8153 11.685 19.6746 11.5443C19.534 11.4037 19.4549 11.2129 19.4549 11.014V8.80399C19.4613 8.54462 19.4172 8.28649 19.3249 8.04399L12.0249 12.424V20.614C12.1398 20.5798 12.2505 20.5329 12.3549 20.474L14.8849 19.074C15.0572 18.986 15.2566 18.967 15.4424 19.0208C15.6282 19.0747 15.7865 19.1975 15.8849 19.364C15.9746 19.537 15.9935 19.7381 15.9375 19.9248C15.8815 20.1114 15.7551 20.2689 15.5849 20.364L13.0649 21.764C12.5118 22.0731 11.8886 22.2349 11.2549 22.234C10.6185 22.2322 9.99268 22.0706 9.43494 21.764L3.48494 18.464C2.90593 18.1356 2.42363 17.6604 2.08658 17.0864C1.74953 16.5123 1.56961 15.8597 1.56494 15.194V8.80399C1.56494 8.13499 1.74494 7.47899 2.08494 6.90399C2.14227 6.80066 2.20894 6.70399 2.28494 6.61399C2.59696 6.16685 3.00751 5.79734 3.48494 5.53399L9.48494 2.22399C10.041 1.92378 10.663 1.7666 11.2949 1.7666C11.9269 1.7666 12.5489 1.92378 13.1049 2.22399L19.1049 5.53399C19.5249 5.76499 19.8939 6.08199 20.1849 6.46399C20.2296 6.50999 20.2696 6.55999 20.3049 6.61399C20.3809 6.70399 20.4476 6.80066 20.5049 6.90399C20.8249 7.48399 20.9809 8.14099 20.9549 8.80399Z"/>
                    <path d="M22.4351 16.4241C22.4351 16.623 22.3561 16.8138 22.2154 16.9544C22.0748 17.0951 21.884 17.1741 21.6851 17.1741H19.9251V18.9241C19.9251 19.123 19.8461 19.3138 19.7054 19.4544C19.5648 19.5951 19.374 19.6741 19.1751 19.6741C18.9762 19.6741 18.7854 19.5951 18.6447 19.4544C18.5041 19.3138 18.4251 19.123 18.4251 18.9241V17.1741H16.7051C16.5062 17.1741 16.3154 17.0951 16.1747 16.9544C16.0341 16.8138 15.9551 16.623 15.9551 16.4241C15.9551 16.2252 16.0341 16.0344 16.1747 15.8937C16.3154 15.7531 16.5062 15.6741 16.7051 15.6741H18.4451V13.9241C18.4451 13.7252 18.5241 13.5344 18.6647 13.3937C18.8054 13.2531 18.9962 13.1741 19.1951 13.1741C19.394 13.1741 19.5848 13.2531 19.7254 13.3937C19.8661 13.5344 19.9451 13.7252 19.9451 13.9241V15.6741H21.7051C21.8997 15.6817 22.0839 15.7638 22.2197 15.9034C22.3556 16.043 22.4327 16.2293 22.4351 16.4241Z"/>
                </svg>
            `,
            });
        }

        return visibleTabs;
    });

    const validateProductForm = () => {
        let isValid = true;

        if (!form.value.name || form.value.name.trim() === "") {
            form.value.errors.name = "Nama produk wajib diisi.";
            isValid = false;
        }

        if (!form.value.sku_prefix || form.value.sku_prefix.trim() === "") {
            form.value.errors.sku_prefix = "SKU Prefix wajib diisi.";
            isValid = false;
        }

        return isValid;
    };

    const checkSkuPrefixAvailability = async (
        skuPrefix: string,
        {
            onSuccess = (isAvailable: any) => {},
            onError = (error: any) => {},
            onChangeStatus = (status: string) => {},
        } = {},
    ) => {
        onChangeStatus("loading");

        await axios
            .post(
                `${page.props.ziggy.url}/api/my-store/product-sku-prefix-check`,
                {
                    sku_prefix: skuPrefix,
                    product_id: product.value ? product.value.id : null,
                },
                {
                    headers: {
                        Authorization: token,
                        "X-Selected-Store-ID": selectedStoreId,
                    },
                },
            )
            .then((response) => {
                onChangeStatus("success");
                const isAvailable = response.data.result.is_available;

                onSuccess(isAvailable);

                if (!isAvailable) {
                    form.value.errors.sku_prefix =
                        "Sudah digunakan oleh produk lain.";
                } else {
                    form.value.errors.sku_prefix = null;
                }
            })
            .catch((error) => {
                onChangeStatus("error");
                onError(error);
                if (error.response?.data?.error) {
                    form.value.errors.sku_prefix = error.response.data.error;
                }
            });
    };

    return {
        product,
        form,
        initializeForm,
        filteredBrands,
        brandSearch,
        filteredCategories,
        categorySearch,
        deleteVariant,
        getVariants,
        submitProduct,
        variantsToDelete,
        showAddLinkForm,
        openAddLinkForm,
        showAddBrandForm,
        showAddCategoryForm,
        tabs,
        validateProductForm,
        categories,
        brands,
        clearNewProductForm,
        checkSkuPrefixAvailability,
    };
});

<script setup lang="ts">
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Dropdown from "@/Components/Dropdown.vue";
import { computed, ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";

interface Platform {
    id: number;
    name: string;
    icon: string;
}

interface ProductLink {
    id?: number | string;
    url?: string;
    product_id?: number;
    platform_id?: number;
    platform?: any;
}

const props = defineProps({
    link: {
        type: Object as () => ProductLink,
        default: null,
    },
});

const emit = defineEmits(["submit", "close"]);

const page = usePage();
const platforms = [
    ...(page.props.platforms as Platform[]).map((platform) => ({
        ...platform,
        icon: "/storage/" + platform.icon,
    })),
];
const filteredPlatforms = computed(() => {
    return platforms.filter((platform) =>
        platform.name.toLowerCase().includes(platformSearch.value.toLowerCase())
    );
});
const platformSearch = ref("");
const isPlatformDropdownOpen = ref(false);

const form = useForm(
    props.link
        ? {
              id: props.link?.id || null,
              url: props.link?.url || null,
              product_id: props.link?.product_id || null,
              platform_id: props.link?.platform_id || null,
              platform: props.link?.platform || null,
          }
        : {
              id: null,
              url: null,
              product_id: null,
              platform_id: null,
              platform: null,
          }
);

function validate() {
    if (form.url == null || form.url.trim() === "") {
        form.errors.url = "URL tidak boleh kosong.";
    } else if (
        !/^(https?:\/\/)?([\w-]+\.)+[\w-]{2,}(\/[^\s]*)?$/i.test(
            form.url.trim()
        )
    ) {
        form.errors.url = "URL tidak valid.";
    } else {
        form.errors.url = null;
    }
}

function submit() {
    validate();
    console.log(form.errors);
    if (form.errors.url) return;

    emit("submit", form.data());
    emit("close");
}
</script>

<template>
    <form @submit.prevent="submit" class="w-full p-2">
        <div class="mb-3 text-lg font-medium text-center text-gray-900">
            {{ props.link ? "Ubah" : "Tambah" }} Tautan Produk
        </div>

        <div class="flex flex-col items-start gap-3">
            <div class="flex flex-col items-start gap-1.5 w-full">
                <InputLabel for="platform_id" value="Platform" />
                <Dropdown
                    id="platform_id"
                    v-model="form.platform_id"
                    :options="platforms"
                    option-label="name"
                    option-value="id"
                    placeholder="Pilih Platform"
                    align="left"
                    class="block w-full"
                    :error="form.errors.platform_id"
                    @update:modelValue="form.errors.platform_id = null"
                    @onOpen="isPlatformDropdownOpen = true"
                    @onClose="isPlatformDropdownOpen = false"
                >
                    <template #trigger>
                        <TextInput
                            :modelValue="
                                form.platform_id && !isPlatformDropdownOpen
                                    ? platforms.find(
                                          (p) => p.id === form.platform_id
                                      )?.name
                                    : platformSearch
                            "
                            @update:modelValue="
                                form.platform_id && !isPlatformDropdownOpen
                                    ? null
                                    : (platformSearch = $event)
                            "
                            class="w-full"
                            placeholder="Pilih Platform"
                        >
                            <template
                                v-if="
                                    form.platform?.icon &&
                                    !isPlatformDropdownOpen
                                "
                                #prefix
                            >
                                <img
                                    :src="form.platform.icon"
                                    alt="Platform Icon"
                                    class="absolute w-5 h-5 transform -translate-y-1/2 left-3 top-1/2"
                                />
                            </template>

                            <template #suffix>
                                <button
                                    v-if="
                                        form.platform_id &&
                                        !isPlatformDropdownOpen
                                    "
                                    type="button"
                                    class="absolute p-[7px] text-gray-400 bg-white rounded-full top-1 right-1 hover:bg-gray-100 transition-all duration-300 ease-in-out"
                                    @click="
                                        form.platform_id = null;
                                        form.platform = null;
                                        platformSearch = '';
                                    "
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="size-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                                <button
                                    v-else
                                    type="button"
                                    class="absolute p-2 top-1.5 right-1"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        class="size-4 fill-gray-400"
                                    >
                                        <path
                                            d="M18.6054 7.3997C18.4811 7.273 18.3335 7.17248 18.1709 7.10389C18.0084 7.0353 17.8342 7 17.6583 7C17.4823 7 17.3081 7.0353 17.1456 7.10389C16.9831 7.17248 16.8355 7.273 16.7112 7.3997L11.4988 12.7028L6.28648 7.3997C6.03529 7.14415 5.69462 7.00058 5.33939 7.00058C4.98416 7.00058 4.64348 7.14415 4.3923 7.3997C4.14111 7.65526 4 8.00186 4 8.36327C4 8.72468 4.14111 9.07129 4.3923 9.32684L10.5585 15.6003C10.6827 15.727 10.8304 15.8275 10.9929 15.8961C11.1554 15.9647 11.3296 16 11.5055 16C11.6815 16 11.8557 15.9647 12.0182 15.8961C12.1807 15.8275 12.3284 15.727 12.4526 15.6003L18.6188 9.32684C19.1293 8.80747 19.1293 7.93274 18.6054 7.3997Z"
                                        />
                                    </svg>
                                </button>
                            </template>
                        </TextInput>
                    </template>
                    <template #content>
                        <ul class="w-full overflow-y-auto max-h-36">
                            <li
                                v-for="platform in filteredPlatforms"
                                :key="platform.id"
                                @click="
                                    form.platform_id = platform.id;
                                    form.platform = platform;
                                    platformSearch = '';
                                "
                                class="flex items-center gap-2 px-4 py-2 cursor-pointer hover:bg-gray-100 text-start"
                            >
                                <img
                                    v-if="platform.icon"
                                    :src="platform.icon"
                                    alt="Platform Icon"
                                    class="inline-block w-5 h-5 mr-2"
                                />
                                <p>{{ platform.name }}</p>
                            </li>
                        </ul>
                    </template>
                </Dropdown>
            </div>

            <div class="flex flex-col items-start gap-1.5 w-full">
                <InputLabel for="url" value="URL" />
                <TextInput
                    id="url"
                    v-model="form.url"
                    type="text"
                    placeholder="Masukkan URL Produk"
                    class="block w-full"
                    required
                    :autofocus="true"
                    :error="form.errors.url"
                    @update:modelValue="form.errors.url = null"
                />
            </div>
        </div>

        <div class="flex justify-center w-full gap-4 mt-6 text-base">
            <SecondaryButton type="button" @click="emit('close')">
                Batalkan
            </SecondaryButton>
            <PrimaryButton type="submit"> Simpan </PrimaryButton>
        </div>
    </form>
</template>

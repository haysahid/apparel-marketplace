<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

interface Link {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps({
    links: {
        type: Array as () => Link[],
        default: () => [] as Link[],
    },
    isApi: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["change"]);

const activePage = computed(() => {
    return parseInt(
        props.links.find((link) => link.active)?.label.replace(/[^0-9]/g, "") ||
            "1"
    );
});
</script>

<template>
    <div class="flex justify-start">
        <nav
            class="flex items-center gap-2 sm:gap-2.5 text-gray-600 [&>*]:rounded-md [&>*]:size-[38px] [&>*]:aspect-square [&>*]:sm:rounded-lg [&>*]:sm:font-medium"
        >
            <template v-for="(link, index) in props.links" :key="index">
                <template v-if="props.isApi">
                    <!-- <button
                        v-if="link.label.includes('Previous')"
                        type="button"
                        class="flex items-center justify-center transition duration-300 aspect-square hover:bg-primary/10 text-primary bg-secondary-box group"
                        :class="{
                            'text-gray-500 !bg-secondary-box cursor-default pointer-events-none':
                                link.active || link.url === null,
                        }"
                        @click="
                            if (link.url != null) {
                                emit('change', activePage - 1);
                            }
                        "
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            class="fill-gray-300"
                            :class="{
                                '!fill-gray-500 group-hover:!fill-primary':
                                    link.url != null,
                            }"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M11.7803 5.21967C12.0732 5.51256 12.0732 5.98744 11.7803 6.28033L8.06066 10L11.7803 13.7197C12.0732 14.0126 12.0732 14.4874 11.7803 14.7803C11.4874 15.0732 11.0126 15.0732 10.7197 14.7803L6.46967 10.5303C6.17678 10.2374 6.17678 9.76256 6.46967 9.46967L10.7197 5.21967C11.0126 4.92678 11.4874 4.92678 11.7803 5.21967Z"
                            />
                        </svg>
                    </button> -->
                    <button
                        v-if="
                            !link.label.includes('Next') &&
                            !link.label.includes('Previous')
                        "
                        type="button"
                        :class="[
                            'aspect-square flex items-center justify-center transition duration-300',
                            link.active
                                ? 'bg-primary text-white  active:bg-primary/90 focus:bg-primary focus:ring-primary pointer-events-none'
                                : 'text-gray-500 bg-secondary-box  hover:bg-primary/10 hover:text-primary',
                        ]"
                        @click="
                            if (link.url != null && !link.active) {
                                emit(
                                    'change',
                                    link.label.replace(/[^0-9]/g, '')
                                );
                            }
                        "
                    >
                        <span v-html="link.label" />
                    </button>
                    <button
                        v-if="link.label.includes('Next')"
                        type="button"
                        class="flex items-center justify-center transition duration-300 aspect-square hover:bg-primary/10 text-primary bg-secondary-box group"
                        :class="{
                            'text-gray-500 !bg-secondary-box cursor-default pointer-events-none':
                                link.active || link.url === null,
                        }"
                        @click="
                            if (link.url != null) {
                                emit('change', activePage + 1);
                            }
                        "
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            class="fill-gray-300"
                            :class="{
                                '!fill-gray-500 group-hover:!fill-primary':
                                    link.url != null,
                            }"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M8.21967 5.21967C8.51256 4.92678 8.98744 4.92678 9.28033 5.21967L13.5303 9.46967C13.8232 9.76256 13.8232 10.2374 13.5303 10.5303L9.28033 14.7803C8.98744 15.0732 8.51256 15.0732 8.21967 14.7803C7.92678 14.4874 7.92678 14.0126 8.21967 13.7197L11.9393 10L8.21967 6.28033C7.92678 5.98744 7.92678 5.51256 8.21967 5.21967Z"
                            />
                        </svg>
                    </button>
                </template>

                <template v-else>
                    <!-- <Link
                        v-if="link.label.includes('Previous')"
                        :href="link.url === null ? '#' : link.url"
                        class="flex items-center justify-center transition duration-300 aspect-square hover:bg-primary/10 text-primary bg-secondary-box group"
                        :class="{
                            'text-gray-500 !bg-secondary-box cursor-default pointer-events-none':
                                link.active || link.url === null,
                        }"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            class="fill-gray-300"
                            :class="{
                                '!fill-gray-500 group-hover:!fill-primary':
                                    link.url != null,
                            }"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M11.7803 5.21967C12.0732 5.51256 12.0732 5.98744 11.7803 6.28033L8.06066 10L11.7803 13.7197C12.0732 14.0126 12.0732 14.4874 11.7803 14.7803C11.4874 15.0732 11.0126 15.0732 10.7197 14.7803L6.46967 10.5303C6.17678 10.2374 6.17678 9.76256 6.46967 9.46967L10.7197 5.21967C11.0126 4.92678 11.4874 4.92678 11.7803 5.21967Z"
                            />
                        </svg>
                    </Link> -->
                    <Link
                        v-if="
                            !link.label.includes('Next') &&
                            !link.label.includes('Previous')
                        "
                        :href="
                            link.active || link.url === null ? '#' : link.url
                        "
                        :class="[
                            'aspect-square flex items-center justify-center transition duration-300',
                            link.active
                                ? 'bg-primary text-white  active:bg-primary/90 focus:bg-primary focus:ring-primary pointer-events-none'
                                : 'text-gray-500 bg-secondary-box  hover:bg-primary/10 hover:text-primary',
                        ]"
                    >
                        <span v-html="link.label" />
                    </Link>
                    <Link
                        v-if="link.label.includes('Next')"
                        :href="
                            link.active || link.url === null ? '#' : link.url
                        "
                        class="flex items-center justify-center transition duration-300 aspect-square hover:bg-primary/10 text-primary bg-secondary-box group"
                        :class="{
                            'text-gray-500 !bg-secondary-box cursor-default pointer-events-none':
                                link.active || link.url === null,
                        }"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            class="fill-gray-300"
                            :class="{
                                '!fill-gray-500 group-hover:!fill-primary':
                                    link.url != null,
                            }"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M8.21967 5.21967C8.51256 4.92678 8.98744 4.92678 9.28033 5.21967L13.5303 9.46967C13.8232 9.76256 13.8232 10.2374 13.5303 10.5303L9.28033 14.7803C8.98744 15.0732 8.51256 15.0732 8.21967 14.7803C7.92678 14.4874 7.92678 14.0126 8.21967 13.7197L11.9393 10L8.21967 6.28033C7.92678 5.98744 7.92678 5.51256 8.21967 5.21967Z"
                            />
                        </svg>
                    </Link>
                </template>
            </template>
        </nav>
    </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import PrimaryButton from "./PrimaryButton.vue";
import MediaCard from "./MediaCard.vue";

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    mediaList: {
        type: Array as () => Array<TemporaryMediaEntity | MediaEntity>,
        required: true,
    },
});

const emit = defineEmits(["moveToUploadTab"]);

const selectedMediaList = ref<Array<TemporaryMediaEntity | MediaEntity>>([]);

defineExpose({
    selectedMediaList,
});
</script>

<template>
    <div class="flex flex-col items-start h-full">
        <h2 class="mb-4 text-base font-semibold">
            {{ props.title }}
            <span>({{ props.mediaList.length }})</span>
        </h2>

        <!-- Media Thumbnails -->
        <div
            v-if="props.mediaList.length > 0"
            class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4"
        >
            <template
                v-for="(temporaryMedia, index) in props.mediaList"
                :key="temporaryMedia.id"
            >
                <Transition
                    name="fade"
                    mode="out-in"
                    appear
                    @before-enter="
                        (el: HTMLElement) => {
                            el.style.transitionDelay = index * 50 + 'ms';
                        }
                    "
                    @after-enter="
                        (el: HTMLElement) => {
                            el.style.transitionDelay = '';
                        }
                    "
                    @after-leave="
                        (el: HTMLElement) => {
                            el.style.transitionDelay = '';
                        }
                    "
                >
                    <MediaCard
                        :media="temporaryMedia"
                        :showName="false"
                        :showSize="false"
                        :isSelected="
                            selectedMediaList.some(
                                (media) => media.id === temporaryMedia.id,
                            )
                        "
                        @update:isSelected="
                            (value: boolean) => {
                                if (value) {
                                    selectedMediaList.push(temporaryMedia);
                                } else {
                                    const index =
                                        selectedMediaList.indexOf(
                                            temporaryMedia,
                                        );
                                    if (index > -1) {
                                        selectedMediaList.splice(index, 1);
                                    }
                                }
                            }
                        "
                    />
                </Transition>
            </template>
        </div>
        <div
            v-else
            class="flex flex-col items-center justify-center w-full h-full pt-1 pb-6"
        >
            <p class="text-sm text-center text-gray-500">
                Tidak ada media yang ditemukan.
            </p>
            <div class="mt-4">
                <PrimaryButton @click="emit('moveToUploadTab')">
                    Unggah Media
                    <template #prefix>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            class="size-5"
                        >
                            <path
                                d="M11 20H6.5C4.98333 20 3.68767 19.475 2.613 18.425C1.53833 17.375 1.00067 16.0917 1 14.575C1 13.275 1.39167 12.1167 2.175 11.1C2.95833 10.0833 3.98333 9.43333 5.25 9.15C5.66667 7.61667 6.5 6.375 7.75 5.425C9 4.475 10.4167 4 12 4C13.95 4 15.6043 4.67933 16.963 6.038C18.3217 7.39667 19.0007 9.05067 19 11C20.15 11.1333 21.1043 11.6293 21.863 12.488C22.6217 13.3467 23.0007 14.3507 23 15.5C23 16.75 22.5627 17.8127 21.688 18.688C20.8133 19.5633 19.7507 20.0007 18.5 20H13V12.85L14.6 14.4L16 13L12 9L8 13L9.4 14.4L11 12.85V20Z"
                                fill="currentColor"
                            />
                        </svg>
                    </template>
                </PrimaryButton>
            </div>
        </div>
    </div>
</template>

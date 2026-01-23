<script setup lang="ts">
import mediaService from "@/services/my-store/media-service";
import { computed, ref } from "vue";
import TabButton from "./TabButton.vue";
import UploadMedia from "./UploadMedia.vue";
import SelectMedia from "./SelectMedia.vue";
import PrimaryButton from "./PrimaryButton.vue";
import SecondaryButton from "./SecondaryButton.vue";
import temporaryMediaService from "@/services/my-store/temporary-media-service";

const props = defineProps({
    modelType: {
        type: String,
        required: false,
        default: "product",
    },
    modelId: {
        type: Number,
        required: false,
        default: null,
    },
    collectionName: {
        type: String,
        required: false,
        default: null,
    },
    fileType: {
        type: String,
        required: false,
        default: "image",
    },
});

const emit = defineEmits(["close", "selectedMediaList"]);

const mediaListPagination = ref<PaginationModel<MediaEntity>>(null);

const mediaList = computed(() => {
    return mediaListPagination.value ? mediaListPagination.value.data : [];
});
const temporaryMediaList = ref<TemporaryMediaEntity[]>([]);

mediaService().getMediaList(
    {
        modelType: props.modelType,
        modelId: null,
        collectionName: props.collectionName,
    },
    {
        onSuccess: (response) => {
            mediaListPagination.value = response.data.result;
        },
    },
);

const getTemporaryMediaList = async () => {
    temporaryMediaService().getTemporaryMediaList(
        {},
        {
            onSuccess: (response) => {
                temporaryMediaList.value = response.data.result;
            },
        },
    );
};
getTemporaryMediaList();

const tabs = [
    {
        title: "Unggah",
        icon: `
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M11 20H6.5C4.98333 20 3.68767 19.475 2.613 18.425C1.53833 17.375 1.00067 16.0917 1 14.575C1 13.275 1.39167 12.1167 2.175 11.1C2.95833 10.0833 3.98333 9.43333 5.25 9.15C5.66667 7.61667 6.5 6.375 7.75 5.425C9 4.475 10.4167 4 12 4C13.95 4 15.6043 4.67933 16.963 6.038C18.3217 7.39667 19.0007 9.05067 19 11C20.15 11.1333 21.1043 11.6293 21.863 12.488C22.6217 13.3467 23.0007 14.3507 23 15.5C23 16.75 22.5627 17.8127 21.688 18.688C20.8133 19.5633 19.7507 20.0007 18.5 20H13V12.85L14.6 14.4L16 13L12 9L8 13L9.4 14.4L11 12.85V20Z" fill="currentColor"/>
            </svg>
        `,
    },
    {
        title: "Baru Saja",
        icon: `
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 21C9.9 21 8.04167 20.3627 6.425 19.088C4.80833 17.8133 3.75833 16.184 3.275 14.2C3.20833 13.95 3.25833 13.721 3.425 13.513C3.59167 13.305 3.81667 13.184 4.1 13.15C4.36667 13.1167 4.60833 13.1667 4.825 13.3C5.04167 13.4333 5.19167 13.6333 5.275 13.9C5.675 15.4 6.5 16.625 7.75 17.575C9 18.525 10.4167 19 12 19C13.95 19 15.6043 18.321 16.963 16.963C18.3217 15.605 19.0007 13.9507 19 12C18.9993 10.0493 18.3203 8.39533 16.963 7.038C15.6057 5.68067 13.9513 5.00133 12 5C10.85 5 9.775 5.26667 8.775 5.8C7.775 6.33333 6.93333 7.06667 6.25 8H8C8.28333 8 8.521 8.096 8.713 8.288C8.905 8.48 9.00067 8.71733 9 9C8.99933 9.28267 8.90333 9.52033 8.712 9.713C8.52067 9.90567 8.28333 10.0013 8 10H4C3.71667 10 3.47933 9.904 3.288 9.712C3.09667 9.52 3.00067 9.28267 3 9V5C3 4.71667 3.096 4.47933 3.288 4.288C3.48 4.09667 3.71733 4.00067 4 4C4.28267 3.99933 4.52033 4.09533 4.713 4.288C4.90567 4.48067 5.00133 4.718 5 5V6.35C5.85 5.28333 6.88767 4.45833 8.113 3.875C9.33833 3.29167 10.634 3 12 3C13.25 3 14.421 3.23767 15.513 3.713C16.605 4.18833 17.555 4.82967 18.363 5.637C19.171 6.44433 19.8127 7.39433 20.288 8.487C20.7633 9.57967 21.0007 10.7507 21 12C20.9993 13.2493 20.762 14.4203 20.288 15.513C19.814 16.6057 19.1723 17.5557 18.363 18.363C17.5537 19.1703 16.6037 19.812 15.513 20.288C14.4223 20.764 13.2513 21.0013 12 21ZM13 11.6L15.5 14.1C15.6833 14.2833 15.775 14.5167 15.775 14.8C15.775 15.0833 15.6833 15.3167 15.5 15.5C15.3167 15.6833 15.0833 15.775 14.8 15.775C14.5167 15.775 14.2833 15.6833 14.1 15.5L11.3 12.7C11.2 12.6 11.125 12.4877 11.075 12.363C11.025 12.2383 11 12.109 11 11.975V8C11 7.71667 11.096 7.47933 11.288 7.288C11.48 7.09667 11.7173 7.00067 12 7C12.2827 6.99933 12.5203 7.09533 12.713 7.288C12.9057 7.48067 13.0013 7.718 13 8V11.6Z" fill="currentColor"/>
            </svg>
        `,
    },
    {
        title: "Cari",
        icon: `
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M9.5 16C7.68333 16 6.146 15.3707 4.888 14.112C3.63 12.8533 3.00067 11.316 3 9.5C2.99933 7.684 3.62867 6.14667 4.888 4.888C6.14733 3.62933 7.68467 3 9.5 3C11.3153 3 12.853 3.62933 14.113 4.888C15.373 6.14667 16.002 7.684 16 9.5C16 10.2333 15.8833 10.925 15.65 11.575C15.4167 12.225 15.1 12.8 14.7 13.3L20.3 18.9C20.4833 19.0833 20.575 19.3167 20.575 19.6C20.575 19.8833 20.4833 20.1167 20.3 20.3C20.1167 20.4833 19.8833 20.575 19.6 20.575C19.3167 20.575 19.0833 20.4833 18.9 20.3L13.3 14.7C12.8 15.1 12.225 15.4167 11.575 15.65C10.925 15.8833 10.2333 16 9.5 16ZM9.5 14C10.75 14 11.8127 13.5627 12.688 12.688C13.5633 11.8133 14.0007 10.7507 14 9.5C13.9993 8.24933 13.562 7.187 12.688 6.313C11.814 5.439 10.7513 5.00133 9.5 5C8.24867 4.99867 7.18633 5.43633 6.313 6.313C5.43967 7.18967 5.002 8.252 5 9.5C4.998 10.748 5.43567 11.8107 6.313 12.688C7.19033 13.5653 8.25267 14.0027 9.5 14Z" fill="currentColor"/>
            </svg>
        `,
    },
];
const selectedTabIndex = ref(0);

const uploadMediaTab = ref(null);
const temporaryMediaTab = ref(null);
const mediaTab = ref(null);

const selectedMediaList = computed<Array<TemporaryMediaEntity | MediaEntity>>(
    () => {
        const selectedTemporaryMedia =
            temporaryMediaTab.value?.selectedMediaList || [];
        const selectedMedia = mediaTab.value?.selectedMediaList || [];

        return [...selectedTemporaryMedia, ...selectedMedia];
    },
);

const onUploadCompleted = (uploadedTemporaryMediaList) => {
    temporaryMediaTab.value.selectedMediaList = [
        ...uploadedTemporaryMediaList,
        ...temporaryMediaTab.value.selectedMediaList,
    ];
    getTemporaryMediaList();

    selectedTabIndex.value = 1;
};

const attachStatus = ref<"idle" | "loading" | "success" | "error">("idle");

const selectMediaList = () => {
    attachStatus.value = "loading";

    // Attach selected media to the model
    const mediaIds = selectedMediaList.value
        .filter((media) => !media.is_temporary)
        .map((media) => media.id);

    if (mediaIds.length > 0) {
        mediaService().attachMediaToModel(
            {
                modelType: props.modelType,
                modelId: props.modelId,
                mediaIds: mediaIds,
                collectionName: props.collectionName,
            },
            {
                onSuccess: () => {
                    // Do something if needed
                },
            },
        );
    }

    // Attach selected temporary media to the model
    const temporaryMediaIds = selectedMediaList.value
        .filter((media) => media.is_temporary)
        .map((media) => media.id);

    if (temporaryMediaIds.length > 0) {
        temporaryMediaService().attachTemporaryMediaToModel(
            {
                modelType: props.modelType,
                modelId: props.modelId,
                temporaryMediaIds: temporaryMediaIds,
                collectionName: props.collectionName,
            },
            {
                onSuccess: () => {
                    // Do something if needed
                },
            },
        );
    }

    attachStatus.value = "success";

    emit("selectedMediaList", selectedMediaList.value);
};
</script>

<template>
    <div class="w-full">
        <div class="flex p-4 sm:p-6 sm:gap-x-2 w-fit">
            <TabButton
                v-for="(tab, index) in tabs"
                :key="index"
                :title="tab.title"
                :isActive="selectedTabIndex === index"
                @click="selectedTabIndex = index"
                class="sm:rounded-lg whitespace-nowrap"
            >
                <template #leading v-if="tab.icon">
                    <div class="[&>svg]:size-5" v-html="tab.icon"></div>
                </template>
            </TabButton>
        </div>

        <div class="gap-y-6 overflow-y-auto h-[60vh] px-4 sm:px-6">
            <!-- Upload Section -->
            <UploadMedia
                v-show="selectedTabIndex === 0"
                ref="uploadMediaTab"
                :modelType="props.modelType"
                :modelId="props.modelId"
                :fileType="props.fileType"
                :autoUpload="true"
                @uploadCompleted="onUploadCompleted"
            />

            <!-- Recent Temporary Media -->
            <SelectMedia
                v-show="selectedTabIndex === 1"
                ref="temporaryMediaTab"
                :mediaList="temporaryMediaList"
                title="Media Sementara"
                @moveToUploadTab="selectedTabIndex = 0"
            />

            <!-- Find Media -->
            <SelectMedia
                v-show="selectedTabIndex === 2"
                ref="mediaTab"
                :mediaList="mediaList"
                title="Media"
                @moveToUploadTab="selectedTabIndex = 0"
            />
        </div>

        <!-- Actions -->
        <div
            class="flex items-center gap-4 px-4 py-4 sm:px-6 sm:py-6"
            :class="selectedTabIndex === 0 ? 'justify-end' : 'justify-between'"
        >
            <p v-if="selectedTabIndex !== 0" class="text-sm text-gray-600">
                {{
                    selectedTabIndex === 1
                        ? temporaryMediaTab?.selectedMediaList.length || 0
                        : mediaTab?.selectedMediaList.length || 0
                }}
                media dipilih.
            </p>

            <div class="flex items-center gap-3">
                <SecondaryButton @click="emit('close')">
                    Batal
                </SecondaryButton>

                <PrimaryButton
                    v-if="selectedTabIndex !== 0"
                    :disabled="
                        selectedMediaList.length === 0 ||
                        attachStatus === 'loading'
                    "
                    @click="selectMediaList()"
                >
                    Pilih Media ({{ selectedMediaList.length }})
                </PrimaryButton>
            </div>
        </div>
    </div>
</template>

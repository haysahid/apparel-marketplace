<script setup lang="ts">
const props = defineProps({
    userRolePair: {
        type: Object as () => UserRolePair,
        required: true,
    },
});
const emit = defineEmits({
    updateUserRole: (data: { userId: number; roleSlug: string }) => true,
});
</script>

<template>
    <div
        class="flex items-center justify-between gap-2 p-3 transition-all duration-300 ease-in-out border border-gray-200 rounded-lg hover:border-primary-light group hover:ring-1 hover:ring-primary-light"
    >
        <div class="flex items-center gap-2">
            <img
                v-if="props.userRolePair.user?.avatar"
                :src="$getImageUrl(props.userRolePair.user?.avatar)"
                alt="Profile Photo"
                class="object-cover rounded-full size-10 shrink-0 h-fit"
            />
            <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                width="44"
                height="44"
                viewBox="0 0 44 44"
                class="size-10 h-fit fill-gray-400 shrink-0"
            >
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M40.3333 22.0003C40.3333 32.1258 32.1255 40.3337 22 40.3337C11.8745 40.3337 3.66663 32.1258 3.66663 22.0003C3.66663 11.8748 11.8745 3.66699 22 3.66699C32.1255 3.66699 40.3333 11.8748 40.3333 22.0003ZM27.5 16.5003C27.5 17.959 26.9205 19.358 25.889 20.3894C24.8576 21.4209 23.4586 22.0003 22 22.0003C20.5413 22.0003 19.1423 21.4209 18.1109 20.3894C17.0794 19.358 16.5 17.959 16.5 16.5003C16.5 15.0416 17.0794 13.6427 18.1109 12.6112C19.1423 11.5798 20.5413 11.0003 22 11.0003C23.4586 11.0003 24.8576 11.5798 25.889 12.6112C26.9205 13.6427 27.5 15.0416 27.5 16.5003ZM22 37.5837C25.1465 37.5887 28.2201 36.6366 30.8128 34.8538C31.9201 34.093 32.3931 32.6447 31.7478 31.4658C30.415 29.022 27.665 27.5003 22 27.5003C16.335 27.5003 13.585 29.022 12.2503 31.4658C11.6068 32.6447 12.0798 34.093 13.1871 34.8538C15.7798 36.6366 18.8535 37.5887 22 37.5837Z"
                />
            </svg>
            <div class="flex flex-col items-start">
                <p
                    class="text-sm font-semibold text-gray-900 transition-all duration-300 ease-in-out"
                >
                    <span>{{ props.userRolePair.user?.name }}</span>
                    <span
                        v-if="
                            props.userRolePair.user?.id ===
                            $page.props.auth.user.id
                        "
                        class="ml-1 text-xs italic font-normal text-gray-500"
                    >
                        - Anda
                    </span>
                </p>
                <p class="text-xs text-gray-600">
                    {{ props.userRolePair.user?.email }}
                </p>
            </div>
        </div>

        <slot name="trailing" />
    </div>
</template>

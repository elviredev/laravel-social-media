<script setup>
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import { usePage, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { XMarkIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import { CameraIcon } from '@heroicons/vue/24/solid'
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TabItem from "@/Pages/Profile/Partials/TabItem.vue";
import Edit from "@/Pages/Profile/Edit.vue";

const authUser = usePage().props.auth.user;
const coverImageSrc = ref('')
const avatarImageSrc = ref('')
const showNotification = ref(true)

const props = defineProps({
    errors: Object,
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    success: {
        type: String,
    },
    user: {
        type: Object
    }
});

const imagesForm = useForm({
    avatar: null,
    cover: null,
})


/**
 * Propriété calculée vérifiant que le user est connecté et que c'est le même user qui regarde la
 * page de profil About
 * @type {ComputedRef<unknown>}
 */
const isMyProfile = computed(() => authUser && authUser.id === props.user.id)

/**
 * Modifier l'image bg de profil
 * @param e
 */
function onCoverChange(e) {
    imagesForm.cover = e.target.files[0];
    if (imagesForm.cover) {
        const reader = new FileReader()
        reader.onload = () => {
            coverImageSrc.value = reader.result
        }
        reader.readAsDataURL(imagesForm.cover)
    }
}

/**
 * Modifier l'image avatar
 * @param e
 */
function onAvatarChange(e) {
    imagesForm.avatar = e.target.files[0];
    if (imagesForm.avatar) {
        const reader = new FileReader()
        reader.onload = () => {
            avatarImageSrc.value = reader.result
        }
        reader.readAsDataURL(imagesForm.avatar)
    }
}

/**
 * Annuler image bg de profil
 */
function resetCoverImage() {
    imagesForm.cover = null
    coverImageSrc.value = null
}

/**
 * Annuler image avatar
 */
function resetAvatarImage() {
    imagesForm.avatar = null
    avatarImageSrc.value = null
}

/**
 * Soumettre image bg de profil
 */
function submitCoverImage() {
    imagesForm.post(route('profile.updateImage'), {
        onSuccess: () => {
            resetCoverImage()
            setTimeout(() => {
                showNotification.value = false
            }, 3000)
        }
    })
}

/**
 * Soumettre image avatar
 */
function submitAvatarImage() {
    imagesForm.post(route('profile.updateImage'), {
        onSuccess: () => {
            resetAvatarImage()
            setTimeout(() => {
                showNotification.value = false
            }, 3000)
        }
    })
}

</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-[768px] mx-auto h-full overflow-auto">

            <!-- Notification si succès modification -->
            <div
                v-show="showNotification && success"
                class="my-2 py-2 px-3 font-medium text-sm bg-emerald-500 text-white"
            >
                {{ success }}
            </div>


            <!-- Notification si erreur sur extension de l'image -->
            <div
                v-if="errors.cover"
                class="my-2 py-2 px-3 font-medium text-sm bg-red-500 text-white"
            >
                {{ errors.cover }}
            </div>

            <div class="group relative bg-white">
                <!-- Cover -->
                <img
                    :src="coverImageSrc || user.cover_url || '/image/default_cover.jpg'"
                    alt=""
                    class="w-full h-[200px] object-cover"
                >

                <div class="absolute top-2 right-2">
                    <button
                        v-if="!coverImageSrc"
                        class="bg-gray-50 hover:bg-gray-100 text-gray-800 py-1 px-2 text-xs flex items-center opacity-0 group-hover:opacity-100">
                        <CameraIcon class="w-3 h-3 mr-2" />
                        Update Cover Image
                        <input
                            type="file"
                            class="absolute left-0 top-0 right-0 bottom-0 opacity-0"
                            @change="onCoverChange"
                        >
                    </button>
                    <div v-else class="flex gap-2 bg-white p-2 opacity-0 group-hover:opacity-100">
                        <button
                            @click="resetCoverImage"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-1 px-2 text-xs flex items-center"
                        >
                            <XMarkIcon class="w-3 h-3 mr-2" />
                            Cancel
                        </button>
                        <button
                            @click="submitCoverImage"
                            class="bg-gray-800 hover:bg-gray-900 text-gray-100 py-1 px-2 text-xs flex items-center"
                        >
                            <CheckCircleIcon class="w-3 h-3 mr-2" />
                            Submit
                        </button>
                    </div>
                </div>

                <div class="flex ">
                    <!-- Avatar -->
                    <div
                        class="flex items-center justify-center relative group/avatar -mt-[64px] ml-[48px] w-[128px] h-[128px] rounded-full"
                    >
                        <img
                             :src="avatarImageSrc || user.avatar_url || '/image/default_avatar.webp'"
                             alt=""
                             class="w-full h-full object-cover rounded-full"
                        >
                        <button
                            v-if="!avatarImageSrc"
                            class="absolute left-0 top-0 right-0 bottom-0 bg-black/50 text-gray-200 rounded-full opacity-0 flex items-center justify-center group-hover/avatar:opacity-100">
                            <CameraIcon class="w-8 h-8" />
                            <input
                                type="file"
                                class="absolute left-0 top-0 right-0 bottom-0 opacity-0"
                                @change="onAvatarChange"
                            >
                        </button>
                        <div v-else class="absolute top-1 right-0 flex flex-col gap-2">
                            <button
                                @click="resetAvatarImage"
                                class="w-7 h-7 flex items-center justify-center bg-red-500/80 text-white rounded-full"
                            >
                                <XMarkIcon class="w-5 h-5" />
                            </button>
                            <button
                                @click="submitAvatarImage"
                                class="w-7 h-7 flex items-center justify-center bg-emerald-500/80 text-white rounded-full"
                            >
                                <CheckCircleIcon class="w-5 h-5" />
                            </button>
                        </div>

                    </div>

                    <!-- User Infos -->
                    <div class="flex justify-between items-center flex-1 p-4">
                        <h2 class="font-bold text-lg">{{ user.name }}</h2>
                    </div>
                </div>
            </div>

            <!-- Onglets -->
            <div class="border-t">
                <TabGroup>
                    <TabList class="flex bg-white">
                        <Tab as="template" v-slot="{ selected }">
                            <TabItem text="Posts" :selected="selected" />
                        </Tab>
                        <Tab as="template" v-slot="{ selected }">
                            <TabItem text="Followers" :selected="selected" />
                        </Tab>
                        <Tab as="template" v-slot="{ selected }">
                            <TabItem text="Followings" :selected="selected" />
                        </Tab>
                        <Tab as="template" v-slot="{ selected }">
                            <TabItem text="Photos" :selected="selected" />
                        </Tab>
                        <Tab v-if="isMyProfile" as="template" v-slot="{ selected }">
                            <TabItem text="My Profile" :selected="selected" />
                        </Tab>
                    </TabList>

                    <TabPanels class="mt-2">
                        <TabPanel class="bg-white p-3 shadow">
                            Posts
                        </TabPanel>
                        <TabPanel class="bg-white p-3 shadow">
                            Followers
                        </TabPanel>
                        <TabPanel class="bg-white p-3 shadow">
                            Followings
                        </TabPanel>
                        <TabPanel class="bg-white p-3 shadow">
                            Photos
                        </TabPanel>
                        <TabPanel v-if="isMyProfile">
                            <Edit :must-verify-email="mustVerifyEmail" :status="status"/>
                        </TabPanel>
                    </TabPanels>

                </TabGroup>
            </div>

        </div>
    </AuthenticatedLayout>
</template>


<style scoped>

</style>
